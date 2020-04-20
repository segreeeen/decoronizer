<?php

// http://localhost/chrome/decoronizer/src/localizeBuilder/index.php

require_once('ConfigConstants.php');
require_once('LocaleConstants.php');

require_once('Config.php');
require_once('config_default.php');

require_once('MasterProcessor.php');

require_once('PageRenderer.php');

$config = new Config($config);
$masterProcessor = new MasterProcessor();

$localeMaster = $masterProcessor->processLocaleMaster($config);

$pageRenderer = new PageRenderer();

$pageRenderer->renderHeader();

$replaceDataForLocales = composeReplaceDataForLocales($localeMaster, $pageRenderer, $config);

$replaceDataWithCorrelations = addCorrelations($replaceDataForLocales, $config);

$pageRenderer->renderSeparatorLine();

// create local-folder
$outputPath = $config->getOutputPath();
if (true === is_dir($outputPath)) {
    deleteDir($outputPath);
}
mkdir($outputPath,0777);

//print_r($arr);

$msgMaster = file_get_contents($config->getMsgMasterPath());
$destinationPath = $config->getMsgDestinationPath();
foreach ($replaceDataWithCorrelations as $localeFolderName => $fileContents) {
    
    $folderPath = $outputPath."/".$localeFolderName;
    $pageRenderer->renderWriteFolderInfo($folderPath);
    mkdir($folderPath,0777);
    
    $localeMessageContent = str_replace('{string}',$localeFolderName,$msgMaster);
    file_put_contents($folderPath."/".$destinationPath,$localeMessageContent);
    $pageRenderer->renderWriteFileInfo($destinationPath);

    foreach ($fileContents as $fileBaseName => $content) {
        
        $filename = $folderPath."/".$fileBaseName.".json";
        $json = escape_sequence_decode(build_json($replaceDataWithCorrelations[$localeFolderName][$fileBaseName]));
        $pageRenderer->renderWriteFileInfo($filename);
        file_put_contents($filename,$json);
        
    }

}

$pageRenderer->renderFoot();


/**
 * @param array $localeMaster
 * @param PageRenderer $pageRenderer
 * @param Config $config
 *
 * @return array
 */
function composeReplaceDataForLocales(
    array $localeMaster,
    PageRenderer $pageRenderer,
    Config $config
): array {
    $replaceDataForLocales = [];
    $lastTargetFile = '';

    foreach ($localeMaster as $key => $value) {

        if (isCorrelated($value)) {
            continue;
        }

        /**
         * @var string $currentTargetFile
         */
        $currentTargetFile = $value[LocaleConstants::FOR_FILE];

        if (isNewFile($currentTargetFile, $lastTargetFile)) {
            $pageRenderer->renderNewFileInfo($currentTargetFile);

            $lastTargetFile = $currentTargetFile;
        }

        /**
         * @var array $activeLocaleCodes
         */
        $activeLocaleCodes = $config->getActiveLocales();
        foreach ($activeLocaleCodes as $activeLocaleCode) {

            $correlation = $value[LocaleConstants::CORRELATION];
            $derivativeTable = $config->getDerivativeTable();
            $derivationPatterns = $derivativeTable[$correlation];

            $stringToFind = $localeMaster[$correlation][$activeLocaleCode];
            $replacement = $value[$activeLocaleCode];

            $findAndReplaceData = composeReplaceData($derivationPatterns, $stringToFind, $replacement);

            if (isset($replaceDataForLocales[$activeLocaleCode][$currentTargetFile])) {
                $replaceDataForLocales[$activeLocaleCode][$currentTargetFile] = array_merge(
                    $replaceDataForLocales[$activeLocaleCode][$currentTargetFile],
                    $findAndReplaceData
                );
            } else {
                $replaceDataForLocales[$activeLocaleCode][$currentTargetFile] = $findAndReplaceData;
            }

            $pageRenderer->renderReplaceInfo(
                $localeMaster[$correlation][$activeLocaleCode],
                $activeLocaleCode,
                count($findAndReplaceData)
            );
        }
    }

    return $replaceDataForLocales;
}


/**
 * @param array $derivationPatterns
 * @param string $stringToFind
 * @param string $replacement
 *
 * @return array
 */
function composeReplaceData(array $derivationPatterns, string $stringToFind, string $replacement): array
{
    $replaceData = [];

    foreach ($derivationPatterns as $pattern) {
        $findThis = str_replace('{string}', $stringToFind, $pattern);
        $replaceWith = str_replace('{string}', $replacement, $pattern);

        $replaceData[] = array('f' => $findThis, 'r' => $replaceWith);
    }

    return $replaceData;
}

/**
 * @param array $replaceDataForLocales
 * @param Config $config
 *
 * @return array
 */
function addCorrelations(array $replaceDataForLocales, Config $config): array
{
    $localeCorrelations = $config->getLocaleCorrelations();
    $replaceDataWithCorrelations = [];

    foreach ($localeCorrelations as $localeCorrelation => $base) {
        $replaceDataWithCorrelations[$localeCorrelation] = $replaceDataForLocales[$base];
    }

    return $replaceDataWithCorrelations;
}

/**
 * @param array $value
 *
 * @return bool
 */
function isCorrelated(array $value): bool
{
    $id = $value[LocaleConstants::ID_TEXT];
    $correlation = $value[LocaleConstants::CORRELATION];

    return $id === $correlation;
}

/**
 * @param string $currentTargetFile
 * @param string $lastTargetFile
 *
 * @return bool
 */
function isNewFile(string $currentTargetFile, string $lastTargetFile): bool
{
    return $currentTargetFile != $lastTargetFile;
}

/**
 * @param string $path
 *
 * @return bool
 */
function hasTrailingSlash(string $path): bool
{
    return substr($path, -1) === '/';
}

function escape_sequence_decode(string $str) {

    // [U+D800 - U+DBFF][U+DC00 - U+DFFF]|[U+0000 - U+FFFF]
    $regex = '/\\\u([dD][89abAB][\da-fA-F]{2})\\\u([dD][c-fC-F][\da-fA-F]{2})
              |\\\u([\da-fA-F]{4})/sx';

    return preg_replace_callback($regex, function($matches) {

        if (isset($matches[3])) {
            $cp = hexdec($matches[3]);
        } else {
            $lead = hexdec($matches[1]);
            $trail = hexdec($matches[2]);

            // http://unicode.org/faq/utf_bom.html#utf16-4
            $cp = ($lead << 10) + $trail + 0x10000 - (0xD800 << 10) - 0xDC00;
        }

        // https://tools.ietf.org/html/rfc3629#section-3
        // Characters between U+D800 and U+DFFF are not allowed in UTF-8
        if ($cp > 0xD7FF && 0xE000 > $cp) {
            $cp = 0xFFFD;
        }

        // https://github.com/php/php-src/blob/php-5.6.4/ext/standard/html.c#L471
        // php_utf32_utf8(unsigned char *buf, unsigned k)

        if ($cp < 0x80) {
            return chr($cp);
        } else if ($cp < 0xA0) {
            return chr(0xC0 | $cp >> 6).chr(0x80 | $cp & 0x3F);
        }

        return html_entity_decode('&#'.$cp.';');
    }, $str);
}

function build_json ($dataToEncode='')
{
    if (true === is_array($dataToEncode)) {
        return json_encode(
                $dataToEncode,
                JSON_PRETTY_PRINT,
                JSON_UNESCAPED_UNICODE
        );
    }
}

/**
 * @param string $dirPath
 *
 * @return void
 */
function deleteDir(string $dirPath): void
{
    if (false === is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory.");
    }

    if (false === hasTrailingSlash($dirPath)) {
        $dirPath .= '/';
    }

    $files = glob($dirPath . '*', GLOB_MARK);

    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}
