<?php
/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    PostRocket
 */

class PostRocket {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      PostRocket_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('POSTROCKET_VERSION')) {
            $this->version = POSTROCKET_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'postrocket';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->setup_background_processor();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        // The class responsible for orchestrating the actions and filters of the core plugin
        require_once POSTROCKET_PLUGIN_DIR . 'includes/class-postrocket-loader.php';

        // The class responsible for defining all actions that occur in the admin area
        require_once POSTROCKET_PLUGIN_DIR . 'includes/class-postrocket-admin.php';

        // The class responsible for defining all actions that occur in the public-facing side of the site
        require_once POSTROCKET_PLUGIN_DIR . 'includes/class-postrocket-public.php';

        // The class responsible for handling AJAX requests
        require_once POSTROCKET_PLUGIN_DIR . 'includes/class-postrocket-ajax.php';

        // The class responsible for duplicating jobs
        require_once POSTROCKET_PLUGIN_DIR . 'includes/class-postrocket-job-duplicator.php';

        // The class responsible for background processing
        require_once POSTROCKET_PLUGIN_DIR . 'includes/class-postrocket-background-processor.php';

        // The class responsible for location management
        require_once POSTROCKET_PLUGIN_DIR . 'includes/class-postrocket-location-manager.php';

        // The class responsible for API integration
        require_once POSTROCKET_PLUGIN_DIR . 'includes/class-postrocket-api.php';

        // The class responsible for bulk operations
        require_once POSTROCKET_PLUGIN_DIR . 'includes/class-postrocket-bulk-manager.php';

        $this->loader = new PostRocket_Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new PostRocket_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');

        $plugin_ajax = new PostRocket_Ajax();
        
        // AJAX actions
        $this->loader->add_action('wp_ajax_postrocket_duplicate_job', $plugin_ajax, 'handle_duplicate_job');
        $this->loader->add_action('wp_ajax_postrocket_get_queue_status', $plugin_ajax, 'handle_get_queue_status');
        $this->loader->add_action('wp_ajax_postrocket_process_queue', $plugin_ajax, 'handle_process_queue');
        $this->loader->add_action('wp_ajax_postrocket_save_location_list', $plugin_ajax, 'handle_save_location_list');
        $this->loader->add_action('wp_ajax_postrocket_delete_location_list', $plugin_ajax, 'handle_delete_location_list');
        $this->loader->add_action('wp_ajax_postrocket_get_location_list', $plugin_ajax, 'handle_get_location_list');
        $this->loader->add_action('wp_ajax_postrocket_save_api_key', $plugin_ajax, 'handle_save_api_key');
        $this->loader->add_action('wp_ajax_postrocket_validate_api_key', $plugin_ajax, 'handle_validate_api_key');
        $this->loader->add_action('wp_ajax_postrocket_save_settings', $plugin_ajax, 'handle_save_settings');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new PostRocket_Public($this->get_plugin_name(), $this->get_version());

        // Add noindex tags to duplicated jobs if enabled
        if (get_option('postrocket_noindex_duplicates') === 'yes') {
            $this->loader->add_action('wp_head', $plugin_public, 'add_noindex_tags');
        }

        // Filter out duplicated jobs from frontend if enabled
        if (get_option('postrocket_hide_duplicates') === 'yes') {
            $this->loader->add_filter('pre_get_posts', $plugin_public, 'hide_duplicate_jobs');
        }
    }

    /**
     * Setup background processor and related hooks.
     *
     * @since    1.0.0
     * @access   private
     */
    private function setup_background_processor() {
        $background_processor = new PostRocket_Background_Processor();
        
        // Schedule cron hook for background processing
        if (!wp_next_scheduled('postrocket_background_process_hook')) {
            wp_schedule_event(time(), 'every_minute', 'postrocket_background_process_hook');
        }
        
        $this->loader->add_action('postrocket_background_process_hook', $background_processor, 'process_queue');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    PostRocket_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}