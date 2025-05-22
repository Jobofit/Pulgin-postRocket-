<?php
/**
 * Admin help and support page partial.
 *
 * @since      1.0.0
 * @package    PostRocket
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap postrocket-help-support">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="postrocket-card">
        <h2>Getting Started</h2>
        
        <div class="postrocket-help-section">
            <h3>Overview</h3>
            <p>
                PostRocket enables you to duplicate job listings across multiple locations with powerful background processing capabilities.
                This helps you efficiently manage job postings for positions that are available in multiple locations.
            </p>
            
            <h3>Key Features</h3>
            <ul>
                <li><strong>Job Duplicator:</strong> Duplicate jobs across multiple locations with either manual or auto mode.</li>
                <li><strong>Background Processing:</strong> Process large batches of job duplications in the background.</li>
                <li><strong>Location Manager:</strong> Create and manage reusable lists of locations.</li>
                <li><strong>Scheduling:</strong> Schedule duplicated jobs for future publishing.</li>
                <li><strong>Visibility Settings:</strong> Control the visibility and indexing of duplicated jobs.</li>
            </ul>
        </div>
    </div>
    
    <div class="postrocket-card">
        <h2>How to Use</h2>
        
        <div class="postrocket-help-section">
            <h3>Setting Up</h3>
            <ol>
                <li>First, go to the <a href="<?php echo esc_url(admin_url('admin.php?page=postrocket-settings')); ?>">Settings</a> page and enter your API key.</li>
                <li>Configure your visibility preferences for duplicated jobs.</li>
                <li>Create location lists in the <a href="<?php echo esc_url(admin_url('admin.php?page=postrocket-location-manager')); ?>">Location Manager</a>.</li>
            </ol>
            
            <h3>Duplicating Jobs</h3>
            <ol>
                <li>Go to the <a href="<?php echo esc_url(admin_url('admin.php?page=postrocket-job-manager')); ?>">Job Duplicator</a> page.</li>
                <li>Select the job and company.</li>
                <li>Choose either Manual Mode (enter locations directly) or Auto Mode (select a saved location list).</li>
                <li>Optionally, set a future publish date for the duplicated jobs.</li>
                <li>Click "Duplicate Job" to start the process.</li>
            </ol>
            
            <h3>Background Processing</h3>
            <p>
                For large batches (more than 50 locations), jobs will be processed in the background. You can monitor and manage
                the background processing on the <a href="<?php echo esc_url(admin_url('admin.php?page=postrocket-background')); ?>">Background Processing</a> page.
            </p>
            
            <h3>Bulk Operations</h3>
            <p>
                You can also select multiple jobs from the Jobs listing page and use the "Duplicate with PostRocket" bulk action
                to duplicate several jobs at once.
            </p>
        </div>
    </div>
    
    <div class="postrocket-card">
        <h2>Frequently Asked Questions</h2>
        
        <div class="postrocket-help-section">
            <div class="postrocket-faq-item">
                <h3>How many locations can I process at once?</h3>
                <div class="postrocket-faq-answer">
                    <p>
                        You can process up to 50 locations immediately. For larger batches (up to 500 locations),
                        the plugin will automatically use background processing to prevent server overload.
                    </p>
                </div>
            </div>
            
            <div class="postrocket-faq-item">
                <h3>What happens to the duplicated jobs?</h3>
                <div class="postrocket-faq-answer">
                    <p>
                        The plugin creates new job listings that are identical to the original, but with the location
                        appended to the title and the location field set accordingly. All other meta data and taxonomies
                        are copied from the original job.
                    </p>
                </div>
            </div>
            
            <div class="postrocket-faq-item">
                <h3>Can I schedule duplicated jobs for future publishing?</h3>
                <div class="postrocket-faq-answer">
                    <p>
                        Yes! You can set a future publish date when duplicating jobs, and the duplicated jobs will be
                        scheduled for publishing on that date.
                    </p>
                </div>
            </div>
            
            <div class="postrocket-faq-item">
                <h3>What if the background processing stops?</h3>
                <div class="postrocket-faq-answer">
                    <p>
                        You can manually trigger the background processing on the Background Processing page.
                        If you encounter persistent issues, please contact support.
                    </p>
                </div>
            </div>
            
            <div class="postrocket-faq-item">
                <h3>Can I hide duplicated jobs from search engines?</h3>
                <div class="postrocket-faq-answer">
                    <p>
                        Yes, you can add noindex meta tags to duplicated jobs by enabling this option in the Settings page.
                        You can also hide duplicated jobs from the frontend entirely.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="postrocket-card">
        <h2>Support</h2>
        
        <div class="postrocket-help-section">
            <p>
                If you need assistance with PostRocket, please contact our support team:
            </p>
            
            <ul>
                <li><strong>Email:</strong> <a href="mailto:support@example.com">support@example.com</a></li>
                <li><strong>Website:</strong> <a href="https://example.com/support" target="_blank">https://example.com/support</a></li>
                <li><strong>Documentation:</strong> <a href="https://example.com/docs/postrocket" target="_blank">https://example.com/docs/postrocket</a></li>
            </ul>
            
            <p>
                When contacting support, please provide:
            </p>
            
            <ul>
                <li>Your WordPress version</li>
                <li>Your PHP version</li>
                <li>A detailed description of the issue</li>
                <li>Any error messages you've encountered</li>
            </ul>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // FAQ accordion
        $('.postrocket-faq-item h3').click(function() {
            $(this).next('.postrocket-faq-answer').slideToggle();
            $(this).toggleClass('active');
        });
    });
</script>