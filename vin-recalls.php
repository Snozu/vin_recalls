<?php
/**
 * Plugin Name: Vin Recalls
 * Description: A plugin to search for vehicle recalls using a VIN.
 * Version: 1.0
 * Author: Gemini
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function vin_recalls_shortcode() {
    return '<div id="vin-recalls-react-app"></div>';
}
add_shortcode('vin_recall_search', 'vin_recalls_shortcode');

function vin_recalls_enqueue_scripts() {
    if (is_page() && has_shortcode(get_the_content(), 'vin_recall_search')) {
        wp_enqueue_script('vin-recalls-react', plugin_dir_url(__FILE__) . 'src/index.js', ['wp-element'], '1.0', true);
    }
}
add_action('wp_enqueue_scripts', 'vin_recalls_enqueue_scripts');

// REST API endpoint will be added here later.
