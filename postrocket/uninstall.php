<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    PostRocket
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete all plugin options
delete_option('postrocket_hide_duplicates');
delete_option('postrocket_noindex_duplicates');
delete_option('postrocket_api_key');
delete_option('postrocket_background_queue');
delete_option('postrocket_background_progress');
delete_option('postrocket_background_error');

// Delete all location lists (stored as options)
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'postrocket_location_list_%'");