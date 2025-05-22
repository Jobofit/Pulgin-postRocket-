<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    PostRocket
 */

class PostRocket_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version           The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        $current_screen = get_current_screen();
        
        // Only load styles on plugin pages
        if (strpos($current_screen->id, 'postrocket') !== false) {
            wp_enqueue_style($this->plugin_name, POSTROCKET_PLUGIN_URL . 'assets/css/postrocket-admin.css', array(), $this->version, 'all');
            wp_enqueue_style($this->plugin_name . '-icon', POSTROCKET_PLUGIN_URL . 'assets/css/postrocket-icon.css', array(), $this->version, 'all');
            wp_enqueue_style($this->plugin_name . '-notifications', POSTROCKET_PLUGIN_URL . 'assets/css/postrocket-notifications.css', array(), $this->version, 'all');
            wp_enqueue_style('jquery-ui-datepicker');
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        $current_screen = get_current_screen();
        
        // Only load scripts on plugin pages
        if (strpos($current_screen->id, 'postrocket') !== false) {
            wp_enqueue_script($this->plugin_name, POSTROCKET_PLUGIN_URL . 'assets/js/postrocket-admin.js', array('jquery', 'jquery-ui-datepicker'), $this->version, false);
            
            // Localize script for AJAX
            wp_localize_script($this->plugin_name, 'postrocket_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('postrocket-ajax-nonce'),
                'max_immediate' => 50,
                'max_background' => 500,
                'batch_size' => 25
            ));
            
            // Load chart.js on dashboard page
            if (isset($_GET['page']) && $_GET['page'] === 'postrocket') {
                wp_enqueue_script($this->plugin_name . '-chart', POSTROCKET_PLUGIN_URL . 'assets/js/chart.min.js', array(), $this->version, false);
            }
            
            // Load background processing JS on background page
            if (isset($_GET['page']) && $_GET['page'] === 'postrocket-background') {
                wp_enqueue_script($this->plugin_name . '-background', POSTROCKET_PLUGIN_URL . 'assets/js/postrocket-background-processing.js', array('jquery'), $this->version, false);
            }
            
            // Load API popup JS on settings page
            if (isset($_GET['page']) && $_GET['page'] === 'postrocket-settings') {
                wp_enqueue_script($this->plugin_name . '-api', POSTROCKET_PLUGIN_URL . 'assets/js/postrocket-api-popup.js', array('jquery'), $this->version, false);
                wp_enqueue_style($this->plugin_name . '-api', POSTROCKET_PLUGIN_URL . 'assets/css/postrocket-api-popup.css', array(), $this->version, 'all');
            }
        }
    }

    /**
     * Register the admin menu pages.
     *
     * @since    1.0.0
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            'PostRocket',
            'PostRocket',
            'manage_options',
            'postrocket',
            array($this, 'display_dashboard_page'),
            'dashicons-performance',
            30
        );
        
        // Dashboard submenu (same as main menu)
        add_submenu_page(
            'postrocket',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'postrocket',
            array($this, 'display_dashboard_page')
        );
        
        // Job Duplicator submenu
        add_submenu_page(
            'postrocket',
            'Job Duplicator',
            'Job Duplicator',
            'manage_options',
            'postrocket-job-manager',
            array($this, 'display_job_manager_page')
        );
        
        // Location Manager submenu
        add_submenu_page(
            'postrocket',
            'Location Manager',
            'Location Manager',
            'manage_options',
            'postrocket-location-manager',
            array($this, 'display_location_manager_page')
        );
        
        // Background Processing submenu
        add_submenu_page(
            'postrocket',
            'Background Processing',
            'Background Processing',
            'manage_options',
            'postrocket-background',
            array($this, 'display_background_processing_page')
        );
        
        // Settings submenu
        add_submenu_page(
            'postrocket',
            'Settings',
            'Settings',
            'manage_options',
            'postrocket-settings',
            array($this, 'display_settings_page')
        );
        
        // Help & Support submenu
        add_submenu_page(
            'postrocket',
            'Help & Support',
            'Help & Support',
            'manage_options',
            'postrocket-help',
            array($this, 'display_help_page')
        );
    }

    /**
     * Display the dashboard page.
     *
     * @since    1.0.0
     */
    public function display_dashboard_page() {
        require_once POSTROCKET_PLUGIN_DIR . 'includes/partials/postrocket-admin-dashboard.php';
    }

    /**
     * Display the job manager page.
     *
     * @since    1.0.0
     */
    public function display_job_manager_page() {
        require_once POSTROCKET_PLUGIN_DIR . 'includes/partials/postrocket-admin-job-manager.php';
    }

    /**
     * Display the location manager page.
     *
     * @since    1.0.0
     */
    public function display_location_manager_page() {
        require_once POSTROCKET_PLUGIN_DIR . 'includes/partials/postrocket-admin-location-manager.php';
    }

    /**
     * Display the background processing page.
     *
     * @since    1.0.0
     */
    public function display_background_processing_page() {
        require_once POSTROCKET_PLUGIN_DIR . 'includes/partials/postrocket-admin-background-processing.php';
    }

    /**
     * Display the settings page.
     *
     * @since    1.0.0
     */
    public function display_settings_page() {
        require_once POSTROCKET_PLUGIN_DIR . 'includes/partials/postrocket-admin-settings.php';
    }

    /**
     * Display the help page.
     *
     * @since    1.0.0
     */
    public function display_help_page() {
        require_once POSTROCKET_PLUGIN_DIR . 'includes/partials/postrocket-admin-help-support.php';
    }
}