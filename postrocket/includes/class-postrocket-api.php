<?php
/**
 * Handles API integration functionality.
 *
 * @since      1.0.0
 * @package    PostRocket
 */

class PostRocket_API {

    /**
     * API key option name.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $api_key_option    API key option name.
     */
    private $api_key_option = 'postrocket_api_key';

    /**
     * Initialize the class.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // No initialization needed
    }

    /**
     * Validate the API key.
     *
     * @since    1.0.0
     * @param    string    $key    Optional. API key to validate. If not provided, the stored key will be used.
     * @return   bool              True if valid, false otherwise.
     */
    public function validate_api_key($key = null) {
        if ($key === null) {
            $key = get_option($this->api_key_option, '');
        }
        
        if (empty($key)) {
            return false;
        }
        
        // For demonstration purposes, we'll consider any non-empty key as valid
        // In a real implementation, this would make an API call to validate the key
        return true;
    }

    /**
     * Store the API key.
     *
     * @since    1.0.0
     * @param    string    $key    API key to store.
     * @return   bool              True if stored, false otherwise.
     */
    public function store_api_key($key) {
        if (empty($key)) {
            delete_option($this->api_key_option);
            return false;
        }
        
        // Sanitize the key
        $key = sanitize_text_field($key);
        
        // Store the key
        update_option($this->api_key_option, $key);
        
        return true;
    }

    /**
     * Get the API key.
     *
     * @since    1.0.0
     * @return   string    The API key or empty string if not set.
     */
    public function get_api_key() {
        return get_option($this->api_key_option, '');
    }

    /**
     * Make an API request.
     *
     * @since    1.0.0
     * @param    string    $endpoint    API endpoint.
     * @param    array     $params      Request parameters.
     * @param    string    $method      Request method (GET, POST, etc.).
     * @return   array                  API response.
     */
    public function make_request($endpoint, $params = array(), $method = 'GET') {
        // This is a placeholder for actual API integration
        // In a real implementation, this would make HTTP requests to the API
        
        $api_key = $this->get_api_key();
        
        if (empty($api_key)) {
            return array(
                'success' => false,
                'message' => 'API key not set.',
                'error' => 'api_key_missing'
            );
        }
        
        // Simulate a successful API response
        return array(
            'success' => true,
            'data' => array(
                'endpoint' => $endpoint,
                'params' => $params,
                'method' => $method
            ),
            'message' => 'API request successful.'
        );
    }
}