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

/* Add shortcode to include external content */
function parse_file_func( $atts ) {
  /*extract( shortcode_atts( array(
    'file' => 'https://docs.unity3d.com/Manual/class-TextAsset.html'
  ), $atts ) );*/

  /*if ($file!=''){*/
   //echo $atts['file'];
   //echo file_get_contents($atts['file']);
   return file_get_contents($atts['file']);
  /*}else{
    return 'nothing to show';
  }*/

}
add_shortcode( 'parse_file', 'parse_file_func' );

?>
