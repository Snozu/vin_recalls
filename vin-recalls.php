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

// Load environment variables from .env file
function vin_recalls_load_env() {
    $env_file = plugin_dir_path(__FILE__) . '.env';
    if (file_exists($env_file)) {
        $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                if (!defined($key)) {
                    define($key, $value);
                }
            }
        }
        return true;
    }
    return false;
}

// Load environment variables and check if successful
if (!vin_recalls_load_env()) {
    // Only log error in admin area to avoid exposing issues to users
    add_action('admin_notices', function() {
        echo '<div class="error"><p>VIN Recalls Plugin: No se pudo cargar el archivo .env con las credenciales de la base de datos.</p></div>';
    });
}

function vin_recalls_shortcode() {
    return '<div id="vin-recalls-react-app"></div>';
}
add_shortcode('vin_recall_search', 'vin_recalls_shortcode');

function vin_recalls_enqueue_scripts() {
    $asset_file = include(plugin_dir_path(__FILE__) . 'build/index.asset.php');

    wp_enqueue_script(
        'vin-recalls-react',
        plugin_dir_url(__FILE__) . 'build/index.js',
        $asset_file['dependencies'],
        $asset_file['version'],
        true
    );

    wp_enqueue_style(
        'vin-recalls-style',
        plugin_dir_url(__FILE__) . 'build/style-index.css',
        array(),
        $asset_file['version']
    );
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
    
    // Check if all required database credentials are defined
    if (!defined('REMOTE_DB_HOST') || !defined('REMOTE_DB_NAME') || 
        !defined('REMOTE_DB_USER') || !defined('REMOTE_DB_PASSWORD')) {
        return new WP_Error('db_config_error', 'Configuración de base de datos incompleta', array('status' => 500));
    }
    
    // Establish a new connection to the remote database
    $remote_db = new wpdb(REMOTE_DB_USER, REMOTE_DB_PASSWORD, REMOTE_DB_NAME, REMOTE_DB_HOST);

    // Check for connection errors
    if ( $remote_db->error ) {
        return new WP_Error( 'db_connection_error', 'No se pudo conectar a la base de datos remota: ' . $remote_db->error, array( 'status' => 500 ) );
    }

    if (empty($vin)) {
        return new WP_Error('no_vin', 'VIN no válido', ['status' => 400]);
    }

    // Sanitize the VIN input to prevent SQL injection
    $sanitized_vin = $remote_db->_real_escape($vin);

    // Query the remote database
    $results = $remote_db->get_results(
        $remote_db->prepare(
            "SELECT qa_notice_no, created_at FROM recalls_table WHERE vin = %s",
            $sanitized_vin
        )
    );

    if ( $remote_db->last_error ) {
        return new WP_Error( 'db_query_error', 'La consulta a la base de datos falló: ' . $remote_db->last_error, array( 'status' => 500 ) );
    }

    if ( $results && count($results) > 0 ) {
        // Found recall - return success message
        return new WP_REST_Response([
            'vin' => $vin, 
            'hasRecall' => true,
            'message' => 'Su motocicleta tiene llamado a revisión, por favor acude a tu distribuidor autorizado Kawasaki mas cercano'
        ], 200);
    } else {
        // No recall found - return message
        return new WP_REST_Response([
            'vin' => $vin, 
            'hasRecall' => false,
            'message' => 'Su motocicleta no tiene llamado a revisión'
        ], 200);
    }
}
