<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Localize-Builder</title>
  </head>
  <body>


<?

// http://localhost/chrome/decoronizer/src/localizeHelper/index.php


$conf = json_decode(file_get_contents('config.json'),JSON_OBJECT_AS_ARRAY);
// print_r($conf);

$msg_master = file_get_contents($conf['msgmasterpath']);

$data = array_map('str_getcsv', file($conf['sourcecsv']));
array_walk($data, function(&$a) use ($data) {
  $a = array_combine($data[0], $a);
});
array_shift($data); # remove column header

// set csv-IDs to key
foreach ($data as $item) $tmp[$item['id']] = $item;
$data = $tmp;
// print_r($data);

// build structure

foreach ($data as $k => $v) {
 
    
    if ($v['id'] != $v['corr']) {
        
        if ($v['file'] != $filegroup) {
            echo("--------- File ".$v['file'].".json ---------<br>");
            $filegroup = $v['file'];
        }

        //use only the active Locales
        foreach ($conf['activeLocales'] as $localecode) {

            
            // build replace array by replace derivateTable
            foreach ($conf['derivateTable'][$v['corr']] as $pattern) {
                $f_word = str_replace('{string}',$data[$v['corr']][$localecode],$pattern);
                $r_word = str_replace('{string}',$v[$localecode],$pattern);
                $fr_arr[] = array("f" => $f_word,"r" => $r_word);
            }

            if (is_array($arr[$localecode][$v['file']])) {
                $arr[$localecode][$v['file']] = array_merge($arr[$localecode][$v['file']],$fr_arr);
            } else {
                $arr[$localecode][$v['file']] = $fr_arr;
            }
            
            echo("Replace 
            <span><b>".$data[$v['corr']][$localecode]."</b></span> 
            for 
            <span>".$localecode." </span> 
            : 
            <span>".count($fr_arr)."</span> 
            <br>\n");

            unset($fr_arr);
            
        }
    }
}



// Add Local Correlations (en-US etc.)
foreach ($conf['localCorrelations'] as $locCorr => $base)  $temp[$locCorr] = $arr[$base];
$arr = $temp;

echo("<hr>");

// create local-folder
if (is_dir($conf['outputfolder'])) deleteDir($conf['outputfolder']);
mkdir($conf['outputfolder'],0777);

//print_r($arr);

foreach ($arr as $loc_foler_name => $filecontents) {
    
    $path = $conf['outputfolder']."/".$loc_foler_name;
    echo("<hr>");
    echo("Write Folder ".$path ."<br>");
    mkdir($path,0777);
    
    $localmsgcontent = str_replace('{string}',$loc_foler_name,$msg_master);
    file_put_contents($path."/".$conf['msgdestiantionname'],$localmsgcontent);
    echo("Write file ".$conf['msgdestiantionname'] ."<br>");

    foreach ($filecontents as $filebasename => $content) {
        
        $filename = $path."/".$filebasename.".json";
        $json = escape_sequence_decode(build_json($arr[$loc_foler_name][$filebasename]));
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