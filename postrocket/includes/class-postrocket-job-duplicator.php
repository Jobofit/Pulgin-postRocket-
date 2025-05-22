<?php
/**
 * Handles job duplication functionality.
 *
 * @since      1.0.0
 * @package    PostRocket
 */

class PostRocket_Job_Duplicator {

    /**
     * API validation instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      PostRocket_API    $api    The API validation instance.
     */
    private $api;

    /**
     * Initialize the class.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->api = new PostRocket_API();
    }

    /**
     * Duplicate a job across multiple locations.
     *
     * @since    1.0.0
     * @param    int       $job_id          The job ID to duplicate.
     * @param    int       $company_id      The company ID associated with the job.
     * @param    array     $locations       Array of locations to duplicate the job to.
     * @param    string    $schedule_date   Optional. Schedule date for future publishing.
     * @return   array                      Result of the duplication process.
     */
    public function duplicate_job($job_id, $company_id, $locations, $schedule_date = null) {
        // Validate API key
        if (!$this->api->validate_api_key()) {
            return array(
                'success' => false,
                'message' => 'Invalid API key. Please check your settings.',
                'error' => 'invalid_api_key'
            );
        }
        
        // Validate job exists
        $job = get_post($job_id);
        if (!$job || $job->post_type !== 'job_listing') {
            return array(
                'success' => false,
                'message' => 'Invalid job ID. Job not found.',
                'error' => 'invalid_job'
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
        
        // Process based on location count and schedule date
        if ($location_count <= 50 && empty($schedule_date)) {
            // Process immediately
            $result = $this->process_immediate_duplication($job_id, $company_id, $locations);
        } else {
            // Add to background queue
            $result = $this->add_to_background_queue($job_id, $company_id, $locations, $schedule_date);
        }
        
        return $result;
    }

    /**
     * Process immediate job duplication.
     *
     * @since    1.0.0
     * @param    int       $job_id          The job ID to duplicate.
     * @param    int       $company_id      The company ID associated with the job.
     * @param    array     $locations       Array of locations to duplicate the job to.
     * @return   array                      Result of the duplication process.
     */
    private function process_immediate_duplication($job_id, $company_id, $locations) {
        $duplicated = array();
        $errors = array();
        
        foreach ($locations as $location) {
            $result = $this->create_duplicate_job($job_id, $location, $company_id);
            
            if ($result['success']) {
                $duplicated[] = array(
                    'location' => $location,
                    'job_id' => $result['job_id']
                );
            } else {
                $errors[] = array(
                    'location' => $location,
                    'error' => $result['error']
                );
            }
        }
        
        return array(
            'success' => true,
            'mode' => 'immediate',
            'total' => count($locations),
            'duplicated' => count($duplicated),
            'failed' => count($errors),
            'jobs' => $duplicated,
            'errors' => $errors
        );
    }

    /**
     * Add job duplication task to background queue.
     *
     * @since    1.0.0
     * @param    int       $job_id          The job ID to duplicate.
     * @param    int       $company_id      The company ID associated with the job.
     * @param    array     $locations       Array of locations to duplicate the job to.
     * @param    string    $schedule_date   Optional. Schedule date for future publishing.
     * @return   array                      Result of the queue addition.
     */
    private function add_to_background_queue($job_id, $company_id, $locations, $schedule_date = null) {
        // Get current queue
        $queue = get_option('postrocket_background_queue', array());
        
        // Generate unique task ID
        $task_id = uniqid('task_');
        
        // Add new task to queue
        $queue[$task_id] = array(
            'job_id' => $job_id,
            'company_id' => $company_id,
            'locations' => $locations,
            'schedule_date' => $schedule_date,
            'status' => 'pending',
            'processed' => 0,
            'total' => count($locations),
            'success' => 0,
            'failed' => 0,
            'created' => current_time('mysql'),
            'updated' => current_time('mysql')
        );
        
        // Update queue
        update_option('postrocket_background_queue', $queue);
        
        // Ensure cron job is scheduled
        if (!wp_next_scheduled('postrocket_background_process_hook')) {
            wp_schedule_event(time(), 'every_minute', 'postrocket_background_process_hook');
        }
        
        return array(
            'success' => true,
            'mode' => 'background',
            'task_id' => $task_id,
            'total' => count($locations),
            'message' => 'Job duplication added to background queue. ' . count($locations) . ' locations will be processed.'
        );
    }

    /**
     * Create a duplicate job for a specific location.
     *
     * @since    1.0.0
     * @param    int       $job_id          The job ID to duplicate.
     * @param    string    $location        The location for the duplicated job.
     * @param    int       $company_id      The company ID associated with the job.
     * @param    string    $schedule_date   Optional. Schedule date for future publishing.
     * @return   array                      Result of the duplication.
     */
    public function create_duplicate_job($job_id, $location, $company_id, $schedule_date = null) {
        // Get original job
        $original_job = get_post($job_id);
        if (!$original_job) {
            return array(
                'success' => false,
                'error' => 'job_not_found',
                'message' => 'Original job not found.'
            );
        }
        
        // Prepare post data
        $post_data = array(
            'post_title' => $original_job->post_title . ' - ' . $location,
            'post_content' => $original_job->post_content,
            'post_excerpt' => $original_job->post_excerpt,
            'post_status' => empty($schedule_date) ? $original_job->post_status : 'future',
            'post_author' => $original_job->post_author,
            'post_type' => $original_job->post_type,
            'comment_status' => $original_job->comment_status,
            'ping_status' => $original_job->ping_status,
            'post_password' => $original_job->post_password,
            'to_ping' => $original_job->to_ping,
            'pinged' => $original_job->pinged,
            'post_content_filtered' => $original_job->post_content_filtered,
            'menu_order' => $original_job->menu_order,
            'post_mime_type' => $original_job->post_mime_type,
        );
        
        // Set scheduled date if provided
        if (!empty($schedule_date)) {
            $post_data['post_date'] = $schedule_date;
            $post_data['post_date_gmt'] = get_gmt_from_date($schedule_date);
        }
        
        // Insert new post
        $new_job_id = wp_insert_post($post_data, true);
        
        if (is_wp_error($new_job_id)) {
            return array(
                'success' => false,
                'error' => 'insert_failed',
                'message' => $new_job_id->get_error_message()
            );
        }
        
        // Copy post meta
        $this->copy_post_meta($job_id, $new_job_id);
        
        // Add location meta
        update_post_meta($new_job_id, '_job_location', $location);
        
        // Add company meta
        update_post_meta($new_job_id, '_company_id', $company_id);
        
        // Mark as duplicate
        update_post_meta($new_job_id, '_postrocket_is_duplicate', 'yes');
        update_post_meta($new_job_id, '_postrocket_original_job_id', $job_id);
        
        // Copy taxonomies
        $this->copy_taxonomies($job_id, $new_job_id);
        
        return array(
            'success' => true,
            'job_id' => $new_job_id,
            'location' => $location
        );
    }

    /**
     * Copy post meta from original job to duplicate.
     *
     * @since    1.0.0
     * @param    int    $source_id    The source job ID.
     * @param    int    $target_id    The target job ID.
     */
    private function copy_post_meta($source_id, $target_id) {
        // Get all post meta
        $post_meta = get_post_meta($source_id);
        
        // Meta keys to exclude from copying
        $exclude_meta = array(
            '_job_location',
            '_company_id',
            '_postrocket_is_duplicate',
            '_postrocket_original_job_id'
        );
        
        // Copy each meta
        foreach ($post_meta as $key => $values) {
            if (in_array($key, $exclude_meta)) {
                continue;
            }
            
            foreach ($values as $value) {
                update_post_meta($target_id, $key, maybe_unserialize($value));
            }
        }
    }

    /**
     * Copy taxonomies from original job to duplicate.
     *
     * @since    1.0.0
     * @param    int    $source_id    The source job ID.
     * @param    int    $target_id    The target job ID.
     */
    private function copy_taxonomies($source_id, $target_id) {
        // Get all taxonomies for the post type
        $taxonomies = get_object_taxonomies(get_post_type($source_id));
        
        foreach ($taxonomies as $taxonomy) {
            $terms = wp_get_object_terms($source_id, $taxonomy, array('fields' => 'slugs'));
            wp_set_object_terms($target_id, $terms, $taxonomy, false);
        }
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