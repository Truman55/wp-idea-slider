<?php
/*
Plugin Name: IDEA School Слайдер
Description: Слайдер на странице "Программы и цены".
Version: 1.0
Author: Konstantin Trunov
Author URI: http://truman.pro
*/
include 'sliderClass.php';

register_activation_hook(__FILE__, array('WISlider', 'wis_activate'));
register_deactivation_hook(__FILE__, array('WISlider', 'wis_deactivate'));
add_action('wp_ajax_wis_load_image', 'wis_load_image');

$wis_slider = new SliderAdmin;
add_action('admin_menu', array($wis_slider, 'wis_admin_menu'));
add_action('delete_attachment', 'wis_delete_slider_image');

function wis_load_image(){
    SliderAdmin::load_slider_image();
}

function wis_delete_slider_image($image_id) {
    global $wpdb;
    $wpdb->delete( 'ids_slider', array( 'image_id' => $image_id ) );
}