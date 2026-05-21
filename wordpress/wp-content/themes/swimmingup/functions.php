<?php
/**
 * Funcion para enlazar los js y css
 */
function theme_scripts() {
    // Registro y encolado de el main stayle
    wp_register_style('style.css',get_stylesheet_directory_uri().'/css/componente.css',array(),'1.0.0','all');
    wp_enqueue_style('style.css', get_stylesheet_uri(), array(), '1.0.0');
} 

add_action( 'wp_enqueue_scripts', 'theme_scripts' );