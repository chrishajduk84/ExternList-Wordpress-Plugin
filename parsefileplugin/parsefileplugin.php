<?php
   /*
   Plugin Name: Parse File Plugin
   Plugin URI: https://github.com/chrishajduk84/ExternList-Wordpress-Plugin
   description: >-A plugin to use external data (text/html/etc) sources
   Version: 1.0
   Author: Chris Hajduk
   Author URI: https://www.chrishajduk.com
   License: GPL2
   */

require_once("stack.php");

function strip_quotes_space( $val ){
    return preg_replace('/(^[\s\"\']*|[\s\"\']*$)/', '', $val);
}

function str2Array($x){
    $output = "";
    if (is_array($x)){
        foreach ($x as $el){
            $output .= "\n" . str2Array($el);
        }
        return $output;
    }else{
        return (string)$x;
    }
}

/* Add shortcode to include external content */
function parse_file_func( $atts ) {
  /*extract( shortcode_atts( array(
    'file' => 'https://docs.unity3d.com/Manual/class-TextAsset.html'
  ), $atts ) );*/
  
  if ($atts['file']){
    $file = $atts['file'];
    $fileContent = file_get_contents($file);
    //TODO: Check if fileContent is false, indicating failure to copy into memory

    //Check is JSON (parse), else display raw [HTML (display raw) or TEXT (display raw)]
    $json = json_decode($fileContent,true);
    //var_dump($json);
    
    //HTML + JAVASCRIPT FORMATTING
    //TODO: Add filtering by tag capibility (use "$atts['filter']" to determine category)
    

    $pageContent = "";
    foreach ($json as $table){ 
        $pageContent .= "<table><tr>";
        foreach ($table['header'] as $col){
            $pageContent .= "<th>" . $col . "</th>";
        }
        $pageContent .= "</tr>";
        foreach ($table['content'] as $row){
            $pageContent .= "<tr>";
            foreach ($row as $element){
                $elementText = $element;
                if (is_array($element)){
                    $elementText = str2Array($element);
                }

                $pageContent .= "<td>" . $elementText . "</td>";     
                var_dump($element);
            }
            $pageContent .= "</tr>";
        }
        $pageContent .= "</table>";
    }
    return $pageContent;
  }
  return "";

}
add_shortcode( 'parse_file', 'parse_file_func' );

?>
