<?php
/**
 * Plugin Name: Vin Recalls
 * Description: A plugin to search for vehicle recalls using a VIN.
 * Version: 1.0
 * Author: Haziel Zul
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

    // Establish a new connection to the remote database
    $remote_db = new wpdb(REMOTE_DB_USER, REMOTE_DB_PASSWORD, REMOTE_DB_NAME, REMOTE_DB_HOST);

    // Check for connection errors
    if ( $remote_db->error ) {
        return new WP_Error( 'db_connection_error', 'Could not connect to the remote database: ' . $remote_db->error, array( 'status' => 500 ) );
    }

    if (empty($vin)) {
        return new WP_Error('no_vin', 'Invalid VIN', ['status' => 400]);
    }

    // Sanitize the VIN input to prevent SQL injection
    $sanitized_vin = $remote_db->_real_escape($vin);

    // Query the remote database
    $results = $remote_db->get_results(
        $remote_db->prepare(
            "SELECT recall_date, recall_description FROM vin_recalls WHERE vin_number = %s",
            $sanitized_vin
        )
    );

    if ( $remote_db->last_error ) {
        return new WP_Error( 'db_query_error', 'Database query failed: ' . $remote_db->last_error, array( 'status' => 500 ) );
    }

    $recalls = [];
    if ( $results ) {
        foreach ( $results as $row ) {
            $recalls[] = [
                'date'        => $row->recall_date,
                'description' => $row->recall_description,
            ];
        }
    }

    if (empty($recalls)) {
        return new WP_Error('no_recalls_found', 'No recalls found for this VIN.', ['status' => 404]);
    }

    return new WP_REST_Response(['vin' => $vin, 'recalls' => $recalls], 200);
}
