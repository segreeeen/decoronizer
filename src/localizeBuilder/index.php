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
require_once('config_default.php');

// http://localhost/chrome/decoronizer/src/localizeBuilder/index.php

$msgMaster = file_get_contents($config[ConfigConstants::PATH_MSG_MASTER]);

$localeMaster = array_map(
        'str_getcsv',
        file($config[ConfigConstants::PATH_SOURCE_CSV])
);
array_walk($localeMaster, function(&$a) use ($localeMaster) {
  $a = array_combine($localeMaster[0], $a);
});
array_shift($localeMaster); # remove column header

// set csv-IDs to key
$tmp = [];
foreach ($localeMaster as $item) {
    $tmp[$item['id']] = $item;
}
$data = $tmp;
// print_r($data);

// build structure

$fileGroup = '';
foreach ($data as $key => $value) {
 
    
    if ($value['id'] != $value['corr']) {
        
        if ($value['file'] != $fileGroup) {
            echo("--------- File ".$value['file'].".json ---------<br>");
            $fileGroup = $value['file'];
        }

        //use only the active Locales
        foreach ($config[ConfigConstants::LOCALES_ACTIVE] as $localeCode) {

            
            // build replace array by replace derivateTable
            foreach ($config[ConfigConstants::DERIVATIVE_TABLE][$value['corr']] as $pattern) {
                $f_word = str_replace('{string}',$data[$value['corr']][$localeCode],$pattern);
                $r_word = str_replace('{string}',$value[$localeCode],$pattern);
                $fr_arr[] = array("f" => $f_word,"r" => $r_word);
            }

            if (is_array($arr[$localeCode][$value['file']])) {
                $arr[$localeCode][$value['file']] = array_merge($arr[$localeCode][$value['file']],$fr_arr);
            } else {
                $arr[$localeCode][$value['file']] = $fr_arr;
            }
            
            echo("Replace 
            <span><b>".$data[$value['corr']][$localeCode]."</b></span> 
            for 
            <span>".$localeCode." </span> 
            : 
            <span>".count($fr_arr)."</span> 
            <br>\n");

            unset($fr_arr);
            
        }
    }
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
function build_json ($arr='') {

    
    if (is_array($arr))  return json_encode($arr,JSON_PRETTY_PRINT,JSON_UNESCAPED_UNICODE);

}


// --------------------------------------------------------------------------------------
function deleteDir($dirPath) {
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
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


?>



  </body>
</html>