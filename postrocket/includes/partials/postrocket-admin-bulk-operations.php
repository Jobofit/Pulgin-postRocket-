<?php
/**
 * Admin bulk operations page partial.
 *
 * @since      1.0.0
 * @package    PostRocket
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Initialize classes
$bulk_manager = new PostRocket_Bulk_Manager();
$location_manager = new PostRocket_Location_Manager();
$api = new PostRocket_API();

// Check if API key is set
$api_key_set = $api->validate_api_key();

// Get job IDs from transient
$job_ids = $bulk_manager->get_bulk_job_ids();

// Get location lists
$location_lists = $location_manager->get_all_location_lists();

// Get companies
global $wpdb;
$companies_query = "
    SELECT DISTINCT pm.meta_value as company_id, pm2.meta_value as company_name
    FROM {$wpdb->postmeta} pm
    JOIN {$wpdb->postmeta} pm2 ON pm.post_id = pm2.post_id
    WHERE pm.meta_key = '_company_id' 
    AND pm2.meta_key = '_company_name'
    AND pm.meta_value != ''
    ORDER BY pm2.meta_value ASC
";
$companies = $wpdb->get_results($companies_query);
?>

<div class="wrap postrocket-bulk-operations">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php if (!$api_key_set) : ?>
        <div class="notice notice-error">
            <p>
                <strong>API Key Required:</strong> 
                Please <a href="<?php echo esc_url(admin_url('admin.php?page=postrocket-settings')); ?>">set your API key</a> before using bulk operations.
            </p>
        </div>
    <?php endif; ?>
    
    <?php if (empty($job_ids)) : ?>
        <div class="notice notice-warning">
            <p>
                No jobs selected for bulk duplication. Please go to the 
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=job_listing')); ?>">Jobs</a> 
                page, select multiple jobs, and use the "Duplicate with PostRocket" bulk action.
            </p>
        </div>
    <?php else : ?>
        <div class="postrocket-card">
            <h2>Bulk Job Duplication</h2>
            
            <div class="postrocket-bulk-info">
                <p>
                    You have selected <strong><?php echo count($job_ids); ?></strong> jobs for bulk duplication.
                    Please select the duplication options below.
                </p>
            </div>
            
            <form id="postrocket-bulk-form" class="postrocket-form">
                <div class="postrocket-form-section">
                    <h3>Step 1: Select Company</h3>
                    <div class="postrocket-form-row">
                        <label for="postrocket-company">Company:</label>
                        <select id="postrocket-company" name="company_id" required>
                            <option value="">Select a company</option>
                            <?php foreach ($companies as $company) : ?>
                                <option value="<?php echo esc_attr($company->company_id); ?>">
                                    <?php echo esc_html($company->company_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="postrocket-form-help">Select the company to associate with these jobs.</p>
                    </div>
                </div>
                
                <div class="postrocket-form-section">
                    <h3>Step 2: Choose Mode</h3>
                    <div class="postrocket-form-row">
                        <div class="postrocket-radio-group">
                            <label>
                                <input type="radio" name="mode" value="manual" checked> 
                                Manual Mode (Enter locations)
                            </label>
                            <label>
                                <input type="radio" name="mode" value="auto" <?php echo empty($location_lists) ? 'disabled' : ''; ?>> 
                                Auto Mode (Select location list)
                            </label>
                        </div>
                        <p class="postrocket-form-help">Choose how you want to specify locations.</p>
                    </div>
                </div>
                
                <div class="postrocket-form-section" id="postrocket-manual-mode">
                    <h3>Step 3: Enter Locations</h3>
                    <div class="postrocket-form-row">
                        <label for="postrocket-locations">Locations:</label>
                        <textarea id="postrocket-locations" name="locations" rows="5" placeholder="Enter locations separated by commas" required></textarea>
                        <p class="postrocket-form-help">
                            Enter locations separated by commas. Maximum 50 locations for immediate processing, up to 500 for background processing.
                            <span id="postrocket-location-count">0</span> locations entered.
                        </p>
                    </div>
                </div>
                
                <div class="postrocket-form-section" id="postrocket-auto-mode" style="display: none;">
                    <h3>Step 3: Select Location List</h3>
                    <div class="postrocket-form-row">
                        <label for="postrocket-list">Location List:</label>
                        <select id="postrocket-list" name="list_id">
                            <option value="">Select a location list</option>
                            <?php foreach ($location_lists as $list) : ?>
                                <option value="<?php echo esc_attr($list['id']); ?>">
                                    <?php echo esc_html($list['name']); ?> (<?php echo esc_html($list['count']); ?> locations)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="postrocket-form-help">
                            Select a saved location list. <a href="<?php echo esc_url(admin_url('admin.php?page=postrocket-location-manager')); ?>">Manage location lists</a>
                        </p>
                    </div>
                </div>
                
                <div class="postrocket-form-section">
                    <h3>Step 4: Scheduling (Optional)</h3>
                    <div class="postrocket-form-row">
                        <label for="postrocket-schedule">Schedule Date:</label>
                        <input type="text" id="postrocket-schedule" name="schedule_date" placeholder="Click to select a date" autocomplete="off">
                        <p class="postrocket-form-help">
                            Optional: Schedule jobs for future publishing. Leave blank to publish immediately.
                        </p>
                    </div>
                </div>
                
                <div class="postrocket-form-actions">
                    <button type="submit" class="button button-primary" <?php echo !$api_key_set ? 'disabled' : ''; ?>>
                        Process Bulk Duplication
                    </button>
                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=job_listing')); ?>" class="button">
                        Cancel
                    </a>
                    <div class="postrocket-spinner" style="display: none;"></div>
                </div>
            </form>
            
            <div id="postrocket-bulk-result" class="postrocket-result" style="display: none;">
                <h3>Results</h3>
                <div id="postrocket-bulk-result-content"></div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Initialize datepicker
        $('#postrocket-schedule').datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: 0
        });
        
        // Mode toggle
        $('input[name="mode"]').change(function() {
            if ($(this).val() === 'manual') {
                $('#postrocket-manual-mode').show();
                $('#postrocket-auto-mode').hide();
                $('#postrocket-locations').prop('required', true);
                $('#postrocket-list').prop('required', false);
            } else {
                $('#postrocket-manual-mode').hide();
                $('#postrocket-auto-mode').show();
                $('#postrocket-locations').prop('required', false);
                $('#postrocket-list').prop('required', true);
            }
        });
        
        // Location counter
        $('#postrocket-locations').on('input', function() {
            var text = $(this).val();
            var locations = text.split(',').filter(function(item) {
                return item.trim() !== '';
            });
            $('#postrocket-location-count').text(locations.length);
        });
        
        <?php if (!empty($job_ids)) : ?>
        // Form submission
        $('#postrocket-bulk-form').submit(function(e) {
            e.preventDefault();
            
            // Show spinner
            $('.postrocket-spinner').show();
            $('#postrocket-bulk-result').hide();
            
            // Get form data
            var formData = {
                'action': 'postrocket_bulk_duplicate',
                'nonce': postrocket_ajax.nonce,
                'job_ids': <?php echo json_encode($job_ids); ?>,
                'company_id': $('#postrocket-company').val(),
                'mode': $('input[name="mode"]:checked').val(),
                'schedule_date': $('#postrocket-schedule').val()
            };
            
            // Add mode-specific data
            if (formData.mode === 'manual') {
                formData.locations = $('#postrocket-locations').val();
            } else {
                formData.list_id = $('#postrocket-list').val();
            }
            
            // Send AJAX request
            $.post(postrocket_ajax.ajax_url, formData, function(response) {
                $('.postrocket-spinner').hide();
                
                if (response.success) {
                    var result = response.data;
                    var content = '<div class="notice notice-success"><p>' + 
                        'Bulk duplication processed. ' + result.processed + ' out of ' + result.total + ' jobs processed.' +
                        '</p></div>';
                    
                    if (result.added_to_queue > 0) {
                        content += '<p>' + result.added_to_queue + ' jobs added to background queue. ' +
                            'You can monitor the progress on the <a href="' + 
                            '<?php echo esc_url(admin_url('admin.php?page=postrocket-background')); ?>' + 
                            '">Background Processing</a> page.</p>';
                    }
                    
                    if (result.failed > 0) {
                        content += '<p class="postrocket-error">' + result.failed + ' jobs failed to process.</p>';
                    }
                    
                    $('#postrocket-bulk-result-content').html(content);
                } else {
                    var message = response.data && response.data.message ? response.data.message : 'An error occurred.';
                    $('#postrocket-bulk-result-content').html('<div class="notice notice-error"><p>' + message + '</p></div>');
                }
                
                $('#postrocket-bulk-result').show();
            }).fail(function() {
                $('.postrocket-spinner').hide();
                $('#postrocket-bulk-result-content').html('<div class="notice notice-error"><p>Request failed. Please try again.</p></div>');
                $('#postrocket-bulk-result').show();
            });
        });
        <?php endif; ?>
    });
</script>