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

/* Add shortcode to include external content */
function parse_file_func( $atts ) {
  /*extract( shortcode_atts( array(
    'file' => 'https://docs.unity3d.com/Manual/class-TextAsset.html'
  ), $atts ) );*/
  
  if ($atts['file']){
    $file = $atts['file'];
    $fileContent = file_get_contents($file);
    //TODO: Check if fileContent is false, indicating failure to copy into memory

    //Decode JSON (parse), else display raw [HTML (display raw) or TEXT (display raw)]
    $json = json_decode($fileContent,true);
    //var_dump($json);
    
    //HTML + JAVASCRIPT FORMATTING
    //TODO: Add filtering by tag capibility (use "$atts['filter']" to determine category)
    

    $pageContent = "";
    $headerKeys = [];
    $rowCount = 0;
    $filterKeys = array();
    $filter = "tags"; //TODO:TEMPORARY, let shortcode user define this
    //TODO: ALSO INCLUDE A METHOD TO SORT ENTRIES (BY Popularity/Rank?, By Name?) 
    foreach ($json as $table){ 
        $pageContent .= "<table><tr>";
        foreach ($table['header'] as $key=>$col){
            $pageContent .= "<th>" . $col . "</th>";
            array_push($headerKeys,$key);
        }
        $pageContent .= "</tr>";
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
                    //var_dump($element);
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
