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

add_action('rest_api_init', function () {
    register_rest_route('vin-recalls/v1', '/search', [
        'methods' => 'GET',
        'callback' => 'vin_recalls_search_callback',
        'permission_callback' => '__return_true' // Allow public access
    ]);
});

function vin_recalls_search_callback(WP_REST_Request $request) {
    $vin = $request->get_param('vin');

    // --- Database Connection Placeholder ---
    // IMPORTANT: Replace with your actual remote database connection logic.
    // For now, we'll return dummy data.

    if (empty($vin)) {
        return new WP_Error('no_vin', 'Invalid VIN', ['status' => 400]);
    }

    // Dummy data for demonstration
    $dummy_data = [
        'vin' => $vin,
        'recalls' => [
            ['date' => '2023-01-15', 'description' => 'Faulty airbag inflator.'],
            ['date' => '2023-05-20', 'description' => 'Brake system software update required.'],
        ]
    ];

    return new WP_REST_Response($dummy_data, 200);
}
