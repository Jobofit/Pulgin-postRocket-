<?php
/**
 * Handles bulk operations for jobs.
 *
 * @since      1.0.0
 * @package    PostRocket
 */

class PostRocket_Bulk_Manager {

    /**
     * Job duplicator instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      PostRocket_Job_Duplicator    $job_duplicator    The job duplicator instance.
     */
    private $job_duplicator;

    /**
     * Initialize the class.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->job_duplicator = new PostRocket_Job_Duplicator();
        
        // Add bulk actions
        add_filter('bulk_actions-edit-job_listing', array($this, 'register_bulk_actions'));
        add_filter('handle_bulk_actions-edit-job_listing', array($this, 'handle_bulk_actions'), 10, 3);
        add_action('admin_notices', array($this, 'bulk_action_admin_notice'));
    }

    /**
     * Register bulk actions.
     *
     * @since    1.0.0
     * @param    array    $bulk_actions    Existing bulk actions.
     * @return   array                     Modified bulk actions.
     */
    public function register_bulk_actions($bulk_actions) {
        $bulk_actions['postrocket_duplicate'] = __('Duplicate with PostRocket', 'postrocket');
        return $bulk_actions;
    }

    /**
     * Handle bulk actions.
     *
     * @since    1.0.0
     * @param    string    $redirect_to    Redirect URL.
     * @param    string    $action         Bulk action name.
     * @param    array     $post_ids       Selected post IDs.
     * @return   string                    Modified redirect URL.
     */
    public function handle_bulk_actions($redirect_to, $action, $post_ids) {
        if ($action !== 'postrocket_duplicate') {
            return $redirect_to;
        }
        
        // Store the IDs in a transient
        set_transient('postrocket_bulk_job_ids', $post_ids, 60 * 60); // 1 hour expiration
        
        // Redirect to our bulk duplication page
        $redirect_to = admin_url('admin.php?page=postrocket-bulk-operations');
        
        return $redirect_to;
    }

    /**
     * Display admin notice after bulk action.
     *
     * @since    1.0.0
     */
    public function bulk_action_admin_notice() {
        if (!empty($_GET['bulk_duplicated']) && intval($_GET['bulk_duplicated']) > 0) {
            $count = intval($_GET['bulk_duplicated']);
            $message = sprintf(
                _n(
                    '%d job duplicated successfully.',
                    '%d jobs duplicated successfully.',
                    $count,
                    'postrocket'
                ),
                $count
            );
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
        }
    }

    /**
     * Get bulk job IDs from transient.
     *
     * @since    1.0.0
     * @return   array    Array of job IDs or empty array if none.
     */
    public function get_bulk_job_ids() {
        $job_ids = get_transient('postrocket_bulk_job_ids');
        
        if (!$job_ids) {
            return array();
        }
        
        return $job_ids;
    }

    /**
     * Process bulk duplication.
     *
     * @since    1.0.0
     * @param    array     $job_ids         Array of job IDs.
     * @param    int       $company_id      Company ID.
     * @param    array     $locations       Array of locations.
     * @param    string    $schedule_date   Optional. Schedule date for future publishing.
     * @return   array                      Result of the bulk duplication.
     */
    public function process_bulk_duplication($job_ids, $company_id, $locations, $schedule_date = null) {
        $results = array(
            'success' => true,
            'total' => count($job_ids),
            'processed' => 0,
            'failed' => 0,
            'added_to_queue' => 0,
            'details' => array()
        );
        
        foreach ($job_ids as $job_id) {
            $result = $this->job_duplicator->duplicate_job($job_id, $company_id, $locations, $schedule_date);
            
            $results['processed']++;
            
            if ($result['success']) {
                if ($result['mode'] === 'background') {
                    $results['added_to_queue']++;
                }
            } else {
                $results['failed']++;
            }
            
            $results['details'][] = array(
                'job_id' => $job_id,
                'result' => $result
            );
        }
        
        // Clear the transient
        delete_transient('postrocket_bulk_job_ids');
        
        return $results;
    }
}