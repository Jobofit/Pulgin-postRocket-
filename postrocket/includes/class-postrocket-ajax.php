<?php
/**
 * Handles AJAX requests for the plugin.
 *
 * @since      1.0.0
 * @package    PostRocket
 */

class PostRocket_Ajax {

    /**
     * Job duplicator instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      PostRocket_Job_Duplicator    $job_duplicator    The job duplicator instance.
     */
    private $job_duplicator;

    /**
     * Background processor instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      PostRocket_Background_Processor    $background_processor    The background processor instance.
     */
    private $background_processor;

    /**
     * Location manager instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      PostRocket_Location_Manager    $location_manager    The location manager instance.
     */
    private $location_manager;

    /**
     * API integration instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      PostRocket_API    $api    The API integration instance.
     */
    private $api;

    /**
     * Initialize the class.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->job_duplicator = new PostRocket_Job_Duplicator();
        $this->background_processor = new PostRocket_Background_Processor();
        $this->location_manager = new PostRocket_Location_Manager();
        $this->api = new PostRocket_API();
    }

    /**
     * Handle job duplication AJAX request.
     *
     * @since    1.0.0
     */
    public function handle_duplicate_job() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'postrocket-ajax-nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
        }
        
        // Get parameters
        $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
        $company_id = isset($_POST['company_id']) ? intval($_POST['company_id']) : 0;
        $mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : 'manual';
        
        // Get locations based on mode
        $locations = array();
        
        if ($mode === 'manual') {
            $locations_input = isset($_POST['locations']) ? sanitize_text_field($_POST['locations']) : '';
            $locations = array_map('trim', explode(',', $locations_input));
        } else {
            $list_id = isset($_POST['list_id']) ? sanitize_text_field($_POST['list_id']) : '';
            $list = $this->location_manager->get_location_list($list_id);
            
            if ($list && isset($list['locations'])) {
                $locations = $list['locations'];
            }
        }
        
        // Get schedule date
        $schedule_date = isset($_POST['schedule_date']) ? sanitize_text_field($_POST['schedule_date']) : null;
        
        // Validate job ID
        if ($job_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid job ID.'));
        }
        
        // Validate company ID
        if ($company_id <= 0) {
            wp_send_json_error(array('message' => 'Invalid company ID.'));
        }
        
        // Validate locations
        if (empty($locations)) {
            wp_send_json_error(array('message' => 'No locations provided.'));
        }
        
        // Duplicate job
        $result = $this->job_duplicator->duplicate_job($job_id, $company_id, $locations, $schedule_date);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    /**
     * Handle queue status AJAX request.
     *
     * @since    1.0.0
     */
    public function handle_get_queue_status() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'postrocket-ajax-nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
        }
        
        // Get queue status
        $status = $this->background_processor->get_queue_status();
        
        wp_send_json_success($status);
    }

    /**
     * Handle process queue AJAX request.
     *
     * @since    1.0.0
     */
    public function handle_process_queue() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'postrocket-ajax-nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
        }
        
        // Process queue
        $result = $this->background_processor->manual_process();
        
        if ($result) {
            wp_send_json_success(array('message' => 'Queue processing started.'));
        } else {
            wp_send_json_error(array('message' => 'No pending tasks or processing already in progress.'));
        }
    }

    /**
     * Handle save location list AJAX request.
     *
     * @since    1.0.0
     */
    public function handle_save_location_list() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'postrocket-ajax-nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
        }
        
        // Get parameters
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $locations_input = isset($_POST['locations']) ? sanitize_text_field($_POST['locations']) : '';
        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';
        
        // Parse locations
        $locations = array_map('trim', explode(',', $locations_input));
        
        // Save list
        $result = $this->location_manager->save_location_list($name, $locations, $id);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    /**
     * Handle delete location list AJAX request.
     *
     * @since    1.0.0
     */
    public function handle_delete_location_list() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'postrocket-ajax-nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
        }
        
        // Get parameters
        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';
        
        // Delete list
        $result = $this->location_manager->delete_location_list($id);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    /**
     * Handle get location list AJAX request.
     *
     * @since    1.0.0
     */
    public function handle_get_location_list() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'postrocket-ajax-nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
        }
        
        // Get parameters
        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';
        
        // Get list
        $list = $this->location_manager->get_location_list($id);
        
        if ($list) {
            wp_send_json_success($list);
        } else {
            wp_send_json_error(array('message' => 'Location list not found.'));
        }
    }

    /**
     * Handle save API key AJAX request.
     *
     * @since    1.0.0
     */
    public function handle_save_api_key() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'postrocket-ajax-nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
        }
        
        // Get parameters
        $api_key = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';
        
        // Save API key
        $result = $this->api->store_api_key($api_key);
        
        if ($result) {
            wp_send_json_success(array('message' => 'API key saved successfully.'));
        } else {
            wp_send_json_error(array('message' => 'Failed to save API key.'));
        }
    }

    /**
     * Handle validate API key AJAX request.
     *
     * @since    1.0.0
     */
    public function handle_validate_api_key() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'postrocket-ajax-nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
        }
        
        // Get parameters
        $api_key = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';
        
        // Validate API key
        $is_valid = $this->api->validate_api_key($api_key);
        
        if ($is_valid) {
            wp_send_json_success(array('message' => 'API key is valid.'));
        } else {
            wp_send_json_error(array('message' => 'API key is invalid.'));
        }
    }

    /**
     * Handle save settings AJAX request.
     *
     * @since    1.0.0
     */
    public function handle_save_settings() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'postrocket-ajax-nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'You do not have permission to perform this action.'));
        }
        
        // Get parameters
        $hide_duplicates = isset($_POST['hide_duplicates']) ? sanitize_text_field($_POST['hide_duplicates']) : 'no';
        $noindex_duplicates = isset($_POST['noindex_duplicates']) ? sanitize_text_field($_POST['noindex_duplicates']) : 'no';
        
        // Save settings
        update_option('postrocket_hide_duplicates', $hide_duplicates);
        update_option('postrocket_noindex_duplicates', $noindex_duplicates);
        
        wp_send_json_success(array('message' => 'Settings saved successfully.'));
    }
}