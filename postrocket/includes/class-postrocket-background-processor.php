<?php
/**
 * Handles background processing of job duplication tasks.
 *
 * @since      1.0.0
 * @package    PostRocket
 */

class PostRocket_Background_Processor {

    /**
     * Job duplicator instance.
     *
     * @since    1.0.0
     * @access   private
     * @var      PostRocket_Job_Duplicator    $job_duplicator    The job duplicator instance.
     */
    private $job_duplicator;

    /**
     * Batch size for processing.
     *
     * @since    1.0.0
     * @access   private
     * @var      int    $batch_size    Number of items to process in each batch.
     */
    private $batch_size = 25;

    /**
     * Initialize the class.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->job_duplicator = new PostRocket_Job_Duplicator();
    }

    /**
     * Process the background queue.
     *
     * @since    1.0.0
     */
    public function process_queue() {
        // Get queue
        $queue = get_option('postrocket_background_queue', array());
        
        if (empty($queue)) {
            return;
        }
        
        // Set processing flag
        update_option('postrocket_background_progress', 'processing');
        
        // Get first pending task
        $task_id = $this->get_next_pending_task_id($queue);
        
        if ($task_id === false) {
            update_option('postrocket_background_progress', '');
            return;
        }
        
        $task = $queue[$task_id];
        
        // Update task status to processing
        $queue[$task_id]['status'] = 'processing';
        $queue[$task_id]['updated'] = current_time('mysql');
        update_option('postrocket_background_queue', $queue);
        
        // Process batch
        $start_index = $task['processed'];
        $end_index = min($start_index + $this->batch_size, $task['total']);
        
        for ($i = $start_index; $i < $end_index; $i++) {
            $location = $task['locations'][$i];
            
            $result = $this->job_duplicator->create_duplicate_job(
                $task['job_id'],
                $location,
                $task['company_id'],
                $task['schedule_date']
            );
            
            if ($result['success']) {
                $queue[$task_id]['success']++;
            } else {
                $queue[$task_id]['failed']++;
                
                // Store error
                if (!isset($queue[$task_id]['errors'])) {
                    $queue[$task_id]['errors'] = array();
                }
                
                $queue[$task_id]['errors'][] = array(
                    'location' => $location,
                    'error' => $result['error'],
                    'message' => isset($result['message']) ? $result['message'] : ''
                );
            }
            
            $queue[$task_id]['processed']++;
        }
        
        // Update task status
        if ($queue[$task_id]['processed'] >= $task['total']) {
            $queue[$task_id]['status'] = 'completed';
            $queue[$task_id]['completed'] = current_time('mysql');
        } else {
            $queue[$task_id]['status'] = 'pending';
        }
        
        $queue[$task_id]['updated'] = current_time('mysql');
        
        // Update queue
        update_option('postrocket_background_queue', $queue);
        
        // Check if more tasks pending
        $has_pending = $this->has_pending_tasks($queue);
        
        if (!$has_pending) {
            update_option('postrocket_background_progress', '');
        }
    }

    /**
     * Get the ID of the next pending task.
     *
     * @since    1.0.0
     * @param    array    $queue    The queue array.
     * @return   mixed              Task ID or false if no pending tasks.
     */
    private function get_next_pending_task_id($queue) {
        foreach ($queue as $task_id => $task) {
            if ($task['status'] === 'pending') {
                return $task_id;
            }
        }
        
        return false;
    }

    /**
     * Check if there are pending tasks in the queue.
     *
     * @since    1.0.0
     * @param    array    $queue    The queue array.
     * @return   bool               True if pending tasks exist, false otherwise.
     */
    private function has_pending_tasks($queue) {
        foreach ($queue as $task) {
            if ($task['status'] === 'pending') {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get queue status.
     *
     * @since    1.0.0
     * @return   array    Queue status information.
     */
    public function get_queue_status() {
        $queue = get_option('postrocket_background_queue', array());
        $progress = get_option('postrocket_background_progress', '');
        
        $pending_count = 0;
        $processing_count = 0;
        $completed_count = 0;
        
        $total_jobs = 0;
        $processed_jobs = 0;
        
        $current_task = null;
        
        foreach ($queue as $task_id => $task) {
            if ($task['status'] === 'pending') {
                $pending_count++;
            } elseif ($task['status'] === 'processing') {
                $processing_count++;
                $current_task = $task;
                $current_task['id'] = $task_id;
            } elseif ($task['status'] === 'completed') {
                $completed_count++;
            }
            
            $total_jobs += $task['total'];
            $processed_jobs += $task['processed'];
        }
        
        return array(
            'is_processing' => ($progress === 'processing'),
            'pending_tasks' => $pending_count,
            'processing_tasks' => $processing_count,
            'completed_tasks' => $completed_count,
            'total_tasks' => count($queue),
            'total_jobs' => $total_jobs,
            'processed_jobs' => $processed_jobs,
            'current_task' => $current_task,
            'queue' => $queue
        );
    }

    /**
     * Clear completed tasks from the queue.
     *
     * @since    1.0.0
     * @return   int      Number of tasks cleared.
     */
    public function clear_completed_tasks() {
        $queue = get_option('postrocket_background_queue', array());
        $cleared = 0;
        
        foreach ($queue as $task_id => $task) {
            if ($task['status'] === 'completed') {
                unset($queue[$task_id]);
                $cleared++;
            }
        }
        
        update_option('postrocket_background_queue', $queue);
        
        return $cleared;
    }

    /**
     * Manually trigger the queue processing.
     *
     * @since    1.0.0
     * @return   bool     True if processing started, false otherwise.
     */
    public function manual_process() {
        $queue = get_option('postrocket_background_queue', array());
        $progress = get_option('postrocket_background_progress', '');
        
        if (empty($queue) || $progress === 'processing') {
            return false;
        }
        
        // Check if there are pending tasks
        $has_pending = $this->has_pending_tasks($queue);
        
        if (!$has_pending) {
            return false;
        }
        
        // Trigger process
        $this->process_queue();
        
        return true;
    }
}