<?php
/**
 * Plugin Name: PostRocket
 * Plugin URI: https://example.com/postrocket
 * Description: A powerful WordPress plugin for job duplication and management with advanced features. Optimized for performance and background processing.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: postrocket
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package PostRocket
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('POSTROCKET_VERSION', '1.0.0');
define('POSTROCKET_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('POSTROCKET_PLUGIN_URL', plugin_dir_url(__FILE__));
define('POSTROCKET_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_postrocket() {
    // Initialize options
    add_option('postrocket_hide_duplicates', 'no');
    add_option('postrocket_noindex_duplicates', 'no');
    add_option('postrocket_api_key', '');
    
    // Initialize background processor options
    if (false === get_option('postrocket_background_queue')) {
        update_option('postrocket_background_queue', array());
    }
    if (false === get_option('postrocket_background_progress')) {
        update_option('postrocket_background_progress', '');
    }
    if (false === get_option('postrocket_background_error')) {
        update_option('postrocket_background_error', '');
    }
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_postrocket() {
    wp_clear_scheduled_hook('postrocket_background_process_hook');
}

register_activation_hook(__FILE__, 'activate_postrocket');
register_deactivation_hook(__FILE__, 'deactivate_postrocket');

/**
 * The core plugin class.
 */
require_once POSTROCKET_PLUGIN_DIR . 'includes/class-postrocket.php';

/**
 * Begins execution of the plugin.
 */
function run_postrocket() {
    $plugin = new PostRocket();
    $plugin->run();
}
run_postrocket();