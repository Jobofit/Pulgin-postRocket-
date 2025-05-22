<?php
/**
 * Admin background processing page partial.
 *
 * @since      1.0.0
 * @package    PostRocket
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Initialize classes
$background_processor = new PostRocket_Background_Processor();

// Get queue status
$status = $background_processor->get_queue_status();
?>

<div class="wrap postrocket-background-processing">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="postrocket-card">
        <h2>Queue Status</h2>
        
        <div class="postrocket-status-cards">
            <div class="postrocket-status-card">
                <div class="postrocket-status-card-content">
                    <h2><?php echo esc_html($status['pending_tasks']); ?></h2>
                    <p>Pending Tasks</p>
                </div>
                <div class="postrocket-status-card-icon postrocket-status-pending">
                    <span class="dashicons dashicons-clock"></span>
                </div>
            </div>
            
            <div class="postrocket-status-card">
                <div class="postrocket-status-card-content">
                    <h2><?php echo esc_html($status['processing_tasks']); ?></h2>
                    <p>Processing Tasks</p>
                </div>
                <div class="postrocket-status-card-icon postrocket-status-processing">
                    <span class="dashicons dashicons-update"></span>
                </div>
            </div>
            
            <div class="postrocket-status-card">
                <div class="postrocket-status-card-content">
                    <h2><?php echo esc_html($status['completed_tasks']); ?></h2>
                    <p>Completed Tasks</p>
                </div>
                <div class="postrocket-status-card-icon postrocket-status-completed">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
            </div>
            
            <div class="postrocket-status-card">
                <div class="postrocket-status-card-content">
                    <h2><?php echo esc_html($status['processed_jobs']); ?> / <?php echo esc_html($status['total_jobs']); ?></h2>
                    <p>Jobs Processed</p>
                </div>
                <div class="postrocket-status-card-icon">
                    <span class="dashicons dashicons-portfolio"></span>
                </div>
            </div>
        </div>
        
        <div class="postrocket-progress-container">
            <?php if ($status['total_jobs'] > 0) : ?>
                <div class="postrocket-progress">
                    <div class="postrocket-progress-bar" style="width: <?php echo esc_attr(floor(($status['processed_jobs'] / $status['total_jobs']) * 100)); ?>%"></div>
                </div>
                <div class="postrocket-progress-text">
                    <?php echo esc_html(floor(($status['processed_jobs'] / $status['total_jobs']) * 100)); ?>% Complete
                </div>
            <?php else : ?>
                <div class="postrocket-progress">
                    <div class="postrocket-progress-bar" style="width: 0%"></div>
                </div>
                <div class="postrocket-progress-text">
                    No jobs in queue
                </div>
            <?php endif; ?>
        </div>
        
        <div class="postrocket-current-task">
            <h3>Current Processing Task</h3>
            
            <?php if ($status['current_task']) : ?>
                <div class="postrocket-current-task-info">
                    <p>
                        <strong>Job ID:</strong> <?php echo esc_html($status['current_task']['job_id']); ?><br>
                        <strong>Company ID:</strong> <?php echo esc_html($status['current_task']['company_id']); ?><br>
                        <strong>Progress:</strong> <?php echo esc_html($status['current_task']['processed']); ?> of <?php echo esc_html($status['current_task']['total']); ?> locations processed<br>
                        <strong>Success:</strong> <?php echo esc_html($status['current_task']['success']); ?> locations<br>
                        <strong>Failed:</strong> <?php echo esc_html($status['current_task']['failed']); ?> locations<br>
                        <strong>Started:</strong> <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($status['current_task']['created']))); ?>
                    </p>
                </div>
            <?php else : ?>
                <p class="postrocket-no-data">No task currently processing.</p>
            <?php endif; ?>
        </div>
        
        <div class="postrocket-processing-actions">
            <button id="postrocket-process-queue" class="button button-primary" <?php echo ($status['is_processing'] || $status['pending_tasks'] === 0) ? 'disabled' : ''; ?>>
                Process Queue Now
            </button>
            <button id="postrocket-refresh-status" class="button">
                Refresh Status
            </button>
            <div class="postrocket-spinner" style="display: none;"></div>
        </div>
    </div>
    
    <div class="postrocket-card">
        <h2>Queue Details</h2>
        
        <?php if (empty($status['queue'])) : ?>
            <p class="postrocket-no-data">No tasks in queue.</p>
        <?php else : ?>
            <div class="postrocket-table-responsive">
                <table class="postrocket-table">
                    <thead>
                        <tr>
                            <th>Task ID</th>
                            <th>Job ID</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Created</th>
                            <th>Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($status['queue'] as $task_id => $task) : ?>
                            <tr class="postrocket-task-row postrocket-status-<?php echo esc_attr($task['status']); ?>">
                                <td><?php echo esc_html($task_id); ?></td>
                                <td><?php echo esc_html($task['job_id']); ?></td>
                                <td>
                                    <span class="postrocket-status-badge postrocket-status-<?php echo esc_attr($task['status']); ?>">
                                        <?php echo esc_html(ucfirst($task['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo esc_html($task['processed']); ?> / <?php echo esc_html($task['total']); ?>
                                    (<?php echo esc_html(floor(($task['processed'] / $task['total']) * 100)); ?>%)
                                </td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($task['created']))); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($task['updated']))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Process queue button
        $('#postrocket-process-queue').click(function() {
            // Show spinner
            $('.postrocket-spinner').show();
            
            // Disable button
            $(this).prop('disabled', true);
            
            // Send AJAX request
            $.post(postrocket_ajax.ajax_url, {
                'action': 'postrocket_process_queue',
                'nonce': postrocket_ajax.nonce
            }, function(response) {
                $('.postrocket-spinner').hide();
                
                if (response.success) {
                    // Start polling for updates
                    pollQueueStatus();
                } else {
                    alert('Error: ' + (response.data && response.data.message ? response.data.message : 'An error occurred.'));
                    $('#postrocket-process-queue').prop('disabled', false);
                }
            }).fail(function() {
                $('.postrocket-spinner').hide();
                alert('Request failed. Please try again.');
                $('#postrocket-process-queue').prop('disabled', false);
            });
        });
        
        // Refresh status button
        $('#postrocket-refresh-status').click(function() {
            // Show spinner
            $('.postrocket-spinner').show();
            
            // Disable button
            $(this).prop('disabled', true);
            
            // Refresh page
            location.reload();
        });
        
        // Auto-refresh if processing
        if (<?php echo $status['is_processing'] ? 'true' : 'false'; ?>) {
            pollQueueStatus();
        }
        
        // Poll queue status
        function pollQueueStatus() {
            setTimeout(function() {
                $.post(postrocket_ajax.ajax_url, {
                    'action': 'postrocket_get_queue_status',
                    'nonce': postrocket_ajax.nonce
                }, function(response) {
                    if (response.success) {
                        var status = response.data;
                        
                        if (status.is_processing) {
                            // Continue polling
                            pollQueueStatus();
                        } else {
                            // Refresh page
                            location.reload();
                        }
                    } else {
                        // Stop polling on error
                        console.error('Error polling queue status');
                    }
                }).fail(function() {
                    // Stop polling on failure
                    console.error('Failed to poll queue status');
                });
            }, 5000); // Poll every 5 seconds
        }
    });
</script>