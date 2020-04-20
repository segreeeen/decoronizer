<?php
// http://localhost/chrome/decoronizer/src/localizeBuilder/index.php

require_once('ConfigConstants.php');
require_once('LocaleConstants.php');

require_once('Config.php');

require_once('MasterProcessor.php');

require_once('PageRenderer.php');

$config = buildConfig();

$masterProcessor = new MasterProcessor();

$localeMaster = $masterProcessor->processLocaleMaster($config);

$pageRenderer = new PageRenderer($config);

$pageRenderer->renderHeader();

$replacementDataForLocales = composeReplaceDataForLocales($localeMaster, $pageRenderer, $config);

$replacementDataWithCorrelations = addCorrelations($replacementDataForLocales, $config);

$pageRenderer->renderSeparatorLine();

writeOutFiles($replacementDataWithCorrelations, $pageRenderer, $config);

$pageRenderer->renderFoot();

/**
 * @return Config
 */
function buildConfig(): Config
{
    include('config_default.php');

    /** @var array $config */
    return new Config($config);
}

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
    $replacementDataForLocales = [];
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

            if (isset($replacementDataForLocales[$activeLocaleCode][$currentTargetFile])) {
                $replacementDataForLocales[$activeLocaleCode][$currentTargetFile] = array_merge(
                    $replacementDataForLocales[$activeLocaleCode][$currentTargetFile],
                    $findAndReplaceData
                );
            } else {
                $replacementDataForLocales[$activeLocaleCode][$currentTargetFile] = $findAndReplaceData;
            }

            $pageRenderer->renderReplaceInfo(
                $localeMaster[$correlation][$activeLocaleCode],
                $activeLocaleCode,
                count($findAndReplaceData)
            );
        }
    }

    return $replacementDataForLocales;
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
    $replacementData = [];

    foreach ($derivationPatterns as $pattern) {
        $findThis = str_replace('{string}', $stringToFind, $pattern);
        $replaceWith = str_replace('{string}', $replacement, $pattern);

        $replacementData[] = array('f' => $findThis, 'r' => $replaceWith);
    }

    return $replacementData;
}

/**
 * @param array $replacementDataForLocales
 * @param Config $config
 *
 * @return array
 */
function addCorrelations(array $replacementDataForLocales, Config $config): array
{
    $localeCorrelations = $config->getLocaleCorrelations();
    $replacementDataWithCorrelations = [];

    foreach ($localeCorrelations as $localeCorrelation => $base) {
        $replacementDataWithCorrelations[$localeCorrelation] = $replacementDataForLocales[$base];
    }

    return $replacementDataWithCorrelations;
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
 * @param array $replacementDataWithCorrelations
 * @param PageRenderer $pageRenderer
 * @param Config $config
 *
 * @return void
 */
function writeOutFiles(array $replacementDataWithCorrelations, PageRenderer $pageRenderer, Config $config): void
{
    createLocaleFolder($config);

    $msgMaster = file_get_contents($config->getMsgMasterPath());
    $outputPath = $config->getOutputPath();
    $destinationPath = $config->getMsgDestinationPath();

    foreach ($replacementDataWithCorrelations as $localeFolderName => $fileContents) {
        $folderPath = $outputPath . "/" . $localeFolderName;
        $pageRenderer->renderWriteFolderInfo($folderPath);
        mkdir($folderPath,0777);

        $localeMessageContent = str_replace('{string}', $localeFolderName, $msgMaster);
        file_put_contents($folderPath . '/' . $destinationPath, $localeMessageContent);
        $pageRenderer->renderWriteFileInfo($destinationPath);

        foreach ($fileContents as $fileBaseName => $content) {
            $replacementsArray = $replacementDataWithCorrelations[$localeFolderName][$fileBaseName];
            $json = build_json($replacementsArray);

            $filename = $folderPath . '/' . $fileBaseName . '.json';
            $pageRenderer->renderWriteFileInfo($filename);
            file_put_contents($filename, $json);
        }
    }
}

/**
 * @param array $dataToEncode
 *
 * @return string
 */
function build_json (array $dataToEncode): string
{
    $jsonEncodedReplacements = json_encode(
        $dataToEncode,
        JSON_PRETTY_PRINT,
        JSON_UNESCAPED_UNICODE
    );

    $json = escape_sequence_decode($jsonEncodedReplacements);

    return $json;
}

/**
 * @param Config $config
 *
 * @return void
 */
function createLocaleFolder(Config $config): void
{
    $outputPath = $config->getOutputPath();

    if (true === is_dir($outputPath)) {
        deleteDir($outputPath);
    }

    mkdir($outputPath,0777);
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

/**
 * @param string $path
 *
 * @return bool
 */
function hasTrailingSlash(string $path): bool
{
    return substr($path, -1) === '/';
}
