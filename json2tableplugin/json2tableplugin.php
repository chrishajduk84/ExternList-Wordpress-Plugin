<?php
   /*
   Plugin Name: JSON2Table Plugin
   Plugin URI: https://github.com/chrishajduk84/JSON2Table-Wordpress-Plugin
   description: >-A plugin to use external JSON sources to implement a HTML table (acts as a JSON database driver)
   Version: 1.0
   Author: Chris Hajduk
   Author URI: https://www.chrishajduk.com
   License: GPL2
   */

require_once("stack.php");

function register_button_style() {
        wp_register_style( 'button-style', plugins_url( '/css/style.css', __FILE__ ), array(), '1.0.0', 'all' );
}

add_action( 'wp_enqueue_scripts', 'register_button_style' );


function strip_quotes_space( $val ){
    return preg_replace('/(^[\s\"\']*|[\s\"\']*$)/', '', $val);
}

function arr2str($x){
    $output = "";
    if (is_array($x)){
        foreach ($x as $el){
            if ($output == ""){
                $output .= arr2str($el);
            }else{    
                $output .= ", " . arr2str($el);
            }    
        }
        return $output;
    }else{
        return (string)$x;
    }
}

$sortingTag = "";
function compare_rows($x,$y){
    global $sortingTag;
    if (is_string($x[$sortingTag]) && is_string($y[$sortingTag])){
        return strcmp($x[$sortingTag],$y[$sortingTag]);
    }else{
        if ($x[$sortingTag] == $y[$sortingTag]){
            return 0;
        }
        else if ($x[$sortingTag] > $y[$sortingTag]){
            return 1;
        }
        else{
            return -1;
        }
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
    if ($fileContent == false){ return "Error loading data";} //Check if fileContent is false, indicating failure to copy into memory

    //Decode JSON (parse), else display raw [HTML (display raw) or TEXT (display raw)]
    $json = json_decode($fileContent,true);
    
    //HTML + JAVASCRIPT FORMATTING

    $pageContent = "";
    $headerKeys = [];
    $rowCount = 0;
    $filterKeys = array();
    $filter = $atts['filter']; //User defines whether or not filtering will happen
    global $sortingTag;
    $sortingTag = $atts['sort']; //User defines what category will determine how entries are sorted (A is the top, 1 is the top)
    foreach ($json as $table){ 
        $pageContent .= "<table><tr>";
        foreach ($table['header'] as $key=>$col){
            $pageContent .= "<th>" . $col . "</th>";
            array_push($headerKeys,$key);
        }
        $pageContent .= "</tr>";
        //Implement Sorting of data
        if ($sortingTag != ""){
            usort($table['content'],'compare_rows');
        }
        foreach ($table['content'] as $row){
            $rowCount++;
            $pageContent .= "<tr>";
            $rowContent = "";
            $rowFilters = array();
            foreach ($headerKeys as $key){
                $element = $row[$key];
                $elementText = $element;

                //Convert array into string
                if (is_array($element)){
                    $elementText = arr2str($element);
                }

                //Save unique filter keywords
                if ($key == $filter){
                    foreach(explode(",",$elementText) as $tag){
                        $tag = trim($tag);
                        $filterKeys[$tag]++;
                        $rowFilters[$tag]++;
                    }
                }

                //Check if text is a link, could possibly be enabled by JSON field in the future (TODO?)
                if (filter_var($elementText, FILTER_VALIDATE_URL)){
                    $rowContent .= "<td><a href='" . $elementText . "'>" . $elementText . "</a></td>"; 
                }else{
                    $rowContent .= "<td>" . $elementText . "</td>";     
                }

            }
            //Generate row class names - remove spaces and then separate by spaces
            $classNames = "";
            foreach($rowFilters as $tag=>$count){
                $classNames .= "filter_" . preg_replace('/\s+/', '', $tag) . " ";

            }

            $pageContent .= "<tr class='" . $classNames . "'>";
            $pageContent .= $rowContent;
            $pageContent .= "</tr>";
        }
        $pageContent .= "</table>";

        //Filtering Buttons - CSS Filter/Modifier
        $filterContent = "
        <script>
            function filter(tag){
                var elements = document.querySelectorAll('tr[class^=\'filter_\']');
                for(var i=0; i<elements.length; i++){
                    if (tag == 'filter_all'){
                        elements[i].style.display = 'table-row';
                    }else if(elements[i].className.includes(tag)){
                        elements[i].style.display = 'table-row';
                    }else{
                        elements[i].style.display = 'none';
                    }   
                }
            }                
        </script>";
        if ($filter != ""){
            wp_enqueue_style( 'button-style' );
            $filterContent .= "<div><button type='button' class='btnFilter' onclick=filter('filter_all')>All (".$rowCount.")</button>";
            foreach ($filterKeys as $tag=>$count){
                $filterContent .= "<button type='button' class='btnFilter' onclick=filter('filter_".preg_replace('/\s+/', '', $tag)."')>" . $tag . " (".$count.")</button>";
            }
            $filterContent .= "</div><br/>";
        }
    }
    return $filterContent . $pageContent;
  }
  return "";

}
add_shortcode( 'parse_file', 'parse_file_func' );

?>
