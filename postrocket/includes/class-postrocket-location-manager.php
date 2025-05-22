<?php
/**
 * Handles location management functionality.
 *
 * @since      1.0.0
 * @package    PostRocket
 */

class PostRocket_Location_Manager {

    /**
     * Option name prefix for location lists.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $option_prefix    Option name prefix.
     */
    private $option_prefix = 'postrocket_location_list_';

    /**
     * Maximum locations per list.
     *
     * @since    1.0.0
     * @access   private
     * @var      int    $max_locations    Maximum locations per list.
     */
    private $max_locations = 50;

    /**
     * Initialize the class.
     *
     * @since    1.0.0
     */
    public function __construct() {
        // No initialization needed
    }

    /**
     * Save a location list.
     *
     * @since    1.0.0
     * @param    string    $name        The name of the location list.
     * @param    array     $locations   Array of locations.
     * @param    int       $id          Optional. List ID for updating existing lists.
     * @return   array                  Result of the save operation.
     */
    public function save_location_list($name, $locations, $id = null) {
        // Sanitize name
        $name = sanitize_text_field($name);
        
        if (empty($name)) {
            return array(
                'success' => false,
                'message' => 'List name cannot be empty.',
                'error' => 'empty_name'
            );
        }
        
        // Sanitize and deduplicate locations
        $locations = $this->sanitize_locations($locations);
        
        // Check location count
        $location_count = count($locations);
        
        if ($location_count === 0) {
            return array(
                'success' => false,
                'message' => 'No valid locations provided.',
                'error' => 'no_locations'
            );
        }
        
        if ($location_count > $this->max_locations) {
            return array(
                'success' => false,
                'message' => 'Maximum ' . $this->max_locations . ' locations allowed per list.',
                'error' => 'too_many_locations'
            );
        }
        
        // Generate list ID if not provided
        if (empty($id)) {
            $id = uniqid();
        }
        
        // Prepare list data
        $list_data = array(
            'id' => $id,
            'name' => $name,
            'locations' => $locations,
            'count' => $location_count,
            'created' => current_time('mysql'),
            'updated' => current_time('mysql')
        );
        
        // Save list
        $option_name = $this->option_prefix . $id;
        update_option($option_name, $list_data);
        
        return array(
            'success' => true,
            'id' => $id,
            'name' => $name,
            'count' => $location_count,
            'message' => 'Location list saved successfully.'
        );
    }

    /**
     * Delete a location list.
     *
     * @since    1.0.0
     * @param    string    $id    The ID of the location list to delete.
     * @return   array            Result of the delete operation.
     */
    public function delete_location_list($id) {
        $option_name = $this->option_prefix . $id;
        
        // Check if list exists
        if (false === get_option($option_name)) {
            return array(
                'success' => false,
                'message' => 'Location list not found.',
                'error' => 'list_not_found'
            );
        }
        
        // Delete list
        delete_option($option_name);
        
        return array(
            'success' => true,
            'id' => $id,
            'message' => 'Location list deleted successfully.'
        );
    }

    /**
     * Get all location lists.
     *
     * @since    1.0.0
     * @return   array    Array of location lists.
     */
    public function get_all_location_lists() {
        global $wpdb;
        
        $lists = array();
        
        // Get all options with our prefix
        $options = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
                $this->option_prefix . '%'
            )
        );
        
        foreach ($options as $option) {
            $list = maybe_unserialize($option->option_value);
            
            // Skip invalid lists
            if (!is_array($list) || !isset($list['id']) || !isset($list['name'])) {
                continue;
            }
            
            $lists[] = $list;
        }
        
        // Sort by name
        usort($lists, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });
        
        return $lists;
    }

    /**
     * Get a specific location list.
     *
     * @since    1.0.0
     * @param    string    $id    The ID of the location list.
     * @return   mixed            Location list array or false if not found.
     */
    public function get_location_list($id) {
        $option_name = $this->option_prefix . $id;
        $list = get_option($option_name, false);
        
        return $list;
    }

    /**
     * Get all unique locations across all lists.
     *
     * @since    1.0.0
     * @return   array    Array of unique locations.
     */
    public function get_all_unique_locations() {
        $lists = $this->get_all_location_lists();
        $all_locations = array();
        
        foreach ($lists as $list) {
            if (isset($list['locations']) && is_array($list['locations'])) {
                $all_locations = array_merge($all_locations, $list['locations']);
            }
        }
        
        // Remove duplicates and sort
        $unique_locations = array_unique($all_locations);
        sort($unique_locations);
        
        return $unique_locations;
    }

    /**
     * Sanitize and deduplicate locations.
     *
     * @since    1.0.0
     * @param    array    $locations    Raw locations array.
     * @return   array                  Sanitized and deduplicated locations.
     */
    private function sanitize_locations($locations) {
        $clean_locations = array();
        
        foreach ($locations as $location) {
            $location = trim($location);
            
            if (!empty($location)) {
                $clean_locations[] = sanitize_text_field($location);
            }
        }
        
        // Remove duplicates and reindex
        $clean_locations = array_values(array_unique($clean_locations));
        
        return $clean_locations;
    }
}