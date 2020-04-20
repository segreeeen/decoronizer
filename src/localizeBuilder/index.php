<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Localize-Builder</title>
</head>
<body>

<?php
require_once('ConfigConstants.php');
require_once('LocaleConstants.php');

require_once('config_default.php');

// http://localhost/chrome/decoronizer/src/localizeBuilder/index.php

$msgMaster = file_get_contents($config[ConfigConstants::PATH_MSG_MASTER]);

$localeMaster = processLocaleMaster($config);

/**
 * @param array $config
 *
 * @return array
 */
function processLocaleMaster(array $config): array
{
    $localeMaster = loadLocaleMasterData($config);
    $localeMaster = processLocaleMasterData($localeMaster);
    $localeMaster = rebuildWithIdKeys($localeMaster);

    return $localeMaster;
}

/**
 * @param array $config
 *
 * @return array
 */
function loadLocaleMasterData(array $config): array
{
    $localeMaster = array_map(
        'str_getcsv',
        file($config[ConfigConstants::PATH_SOURCE_CSV])
    );

    return $localeMaster;
}

/**
 * @param array $localeMaster
 *
 * @return array
 */
function processLocaleMasterData(array $localeMaster): array
{
    array_walk($localeMaster, function(&$a) use ($localeMaster) {
        $a = array_combine($localeMaster[0], $a);
    });
    array_shift($localeMaster); # remove column header

    return $localeMaster;
}

/**
 * @param array $localeMaster
 *
 * @return array
 */
function rebuildWithIdKeys(array $localeMaster): array
{
    $localeMasterIdKeys = [];

    foreach ($localeMaster as $item) {
        $id = $item['id'];
        $localeMasterIdKeys[$id] = $item;
    }

    return $localeMasterIdKeys;
}

// build structure

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
        $processingNewFileHeader = sprintf('--- %s.json ---<br>', $currentTargetFile);
        echo($processingNewFileHeader);

        $lastTargetFile = $currentTargetFile;
    }

    /**
     * @var array $activeLocaleCodes
     */
    $activeLocaleCodes = $config[ConfigConstants::LOCALES_ACTIVE];
    foreach ($activeLocaleCodes as $activeLocaleCode) {

        $correlation = $value[LocaleConstants::CORRELATION];
        $derivativeTable = $config[ConfigConstants::DERIVATIVE_TABLE];
        $derivationPatterns = $derivativeTable[$correlation];

        $stringToFind = $localeMaster[$correlation][$activeLocaleCode];
        $replacement = $value[$activeLocaleCode];

        // build replace array by replace derivateTable

        foreach ($derivationPatterns as $pattern) {
            $findThis = str_replace('{string}', $stringToFind, $pattern);
            $replaceWith = str_replace('{string}', $replacement, $pattern);

            $findAndReplaceData[] = array('f' => $findThis, 'r' => $replaceWith);
        }

        if (is_array($arr[$activeLocaleCode][$currentTargetFile])) {
            $arr[$activeLocaleCode][$currentTargetFile] = array_merge($arr[$activeLocaleCode][$currentTargetFile], $findAndReplaceData);
        } else {
            $arr[$activeLocaleCode][$currentTargetFile] = $findAndReplaceData;
        }

        echo("Replace 
        <span><b>".$localeMaster[$correlation][$activeLocaleCode]."</b></span> 
        for 
        <span>".$activeLocaleCode." </span> 
        : 
        <span>".count($findAndReplaceData)."</span> 
        <br>\n");

        unset($findAndReplaceData);

    }
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

// Add Local Correlations (en-US etc.)
$localeCorrelations = $config[ConfigConstants::LOCALE_CORRELATIONS];
$temp_arr = [];

foreach ($localeCorrelations as $localeCorrelation => $base) {
    $temp_arr[$localeCorrelation] = $arr[$base];
}

$arr = $temp_arr;

echo("<hr>");

// create local-folder
if (true === is_dir($config[ConfigConstants::PATH_OUTPUT])) {
    deleteDir($config[ConfigConstants::PATH_OUTPUT]);
}
mkdir($config[ConfigConstants::PATH_OUTPUT],0777);

//print_r($arr);

foreach ($arr as $localeFolderName => $fileContents) {
    
    $path = $config[ConfigConstants::PATH_OUTPUT]."/".$localeFolderName;
    echo("<hr>");
    echo("Write Folder ".$path ."<br>");
    mkdir($path,0777);
    
    $localeMessageContent = str_replace('{string}',$localeFolderName,$msgMaster);
    file_put_contents($path."/".$config[ConfigConstants::PATH_MSG_DESTINATION],$localeMessageContent);
    echo("Write file ".$config[ConfigConstants::PATH_MSG_DESTINATION] ."<br>");

    foreach ($fileContents as $fileBaseName => $content) {
        
        $filename = $path."/".$fileBaseName.".json";
        $json = escape_sequence_decode(build_json($arr[$localeFolderName][$fileBaseName]));
        echo("Write file ".$filename ."<br>");
        file_put_contents($filename,$json);
        
    }

}

echo("<hr>");
echo("DONE<br>");

    // --------------------------------------------------------------------------------------

function escape_sequence_decode($str) {

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


// --------------------------------------------------------------------------------------
function build_json ($dataToEncode='') {

    
    if (true === is_array($dataToEncode)) {
        return json_encode(
                $dataToEncode,
                JSON_PRETTY_PRINT,
                JSON_UNESCAPED_UNICODE
        );
    }

}


// --------------------------------------------------------------------------------------
function deleteDir($dirPath) {
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
// --------------------------------------------------------------------------------------

function hasTrailingSlash(string $path): bool
{
    return substr($path, -1) === '/';
}

?>



  </body>
</html>