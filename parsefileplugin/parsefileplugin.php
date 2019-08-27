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
    $decoded = json_decode($fileContent);
    $charArray = str_split($fileContent);
    $isValidJSON = true;
    $syntaxStack = new JSONStack;
    $keyStack = new JSONStack;
    //Value Parsing Variables
    $value = "";
    $collectData = false; //Inital character will not be useful data, variable indicates whether quoted text is present or not
    $inQuotes = false;
    $isKey = true; //All JSON objects start with a key, toggled by ':' and ','
    //Data Storage Variables
    $table = array();
    $tableIndex = 0;
    $row = array();
    foreach ($charArray as $char){
        $stackTop = $syntaxStack->top();//TODO:If is empty, what is the value here?

        if ($char == "\""){ //TODO:Only works for strings and not numbers
            //Is this an Open Quotes character or Close Quotes Character?
            if (!$inQuotes){
                //This is an open quotes, need to listen to upcoming data
                $inQuotes = true;
            }
            else{
                $inQuotes = false;
            }
        }
        
        if ($char == "{" && !$inQuotes){
            $syntaxStack->push("{");
            //Expecting key, start listening
            $collectData = true;
            $isKey = true;
        }
        else if ($char == "[" && !$inQuotes){
            $syntaxStack->push("[");
            //Expecting array, start listening for values
            $collectData = true;
            $isKey = false;
        }
        else if ($char == "}" && !$inQuotes){
            if ($stackTop == "{"){
                //TODO: Save previous data (value)
                //$row[$keyStack->pop()] = strip_quotes_space($value);
                $value = "";
                $syntaxStack->pop();
                $collectData = false;
            }
            else{ throw new RunTimeException("Syntax Error. Unexpected '}'");} 
        }
        else if ($char == "]" && !$inQuotes){
            if ($stackTop == "["){
                //TODO: Save previous data (value)
                //$row[$keyStack->pop()] = strip_quotes_space($value);
                $value = "";
                $syntaxStack->pop();
                $collectData = false;
            }
            else{ throw new RunTimeException("Syntax Error. Unexpected ']'");} 
        }
        else if ($char == ":" && !$inQuotes){
            //Previous data was the key, upcoming data will be the value
            $keyStack->push(strip_quotes_space($value));
            $value = ""; //Clear after saving
            $isKey = false;
            $collectData = true;
        }
        else if ($char == "," && $stackTop == "{" && !$inQuotes){ //NOT TRUE FOR ARRAYS. Need to check that $stackTop is { or [
            //Save previous $value
            $row[$keyStack->pop()] = strip_quotes_space($value);
            $value = ""; //Clear after saving
            $isKey = true;
            $collectData = true;
        }
        else if ($char == "," && $stackTop == "[" && !$inQuotes){ //This is for ARRAYS.
            //TODO: Save previous $value
            //$row[$keyStack->pop()] = strip_quotes_space($value);
            $value = "";
            $isKey = false;
            $collectData = true;
        }
        else if ($collectData == true){
            //Concatenate onto value string
            $value .= $char; 
        }

    }
    
    //HTML + JAVASCRIPT FORMATTING
    
    //$pageContent = "<table><tr><th>Project Name</th><th>Website</th><th>Category</th></tr>";
        //<tr><td>OpenTrons</td><td><a href="https://opentrons.com/">https://opentrons.com/</a></td><td>Liquid Handling</td></tr>
    //foreach ($row in $table){
        //foreach ($element in $row){
            //$pageContent = $row         
        //}
    //}
    //$pageContent .= "</table>";

    return $pageContent;
  }
  return "";

}
add_shortcode( 'parse_file', 'parse_file_func' );

?>
