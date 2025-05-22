<?php
/**
 * Admin dashboard page partial.
 *
 * @since      1.0.0
 * @package    PostRocket
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get metrics for dashboard
global $wpdb;

// Total job count
$total_jobs = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'job_listing'");

// Active job count
$active_jobs = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'job_listing' AND post_status = 'publish'");

// Duplicated job count
$duplicated_jobs = $wpdb->get_var("
    SELECT COUNT(*) FROM {$wpdb->posts} p
    JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
    WHERE p.post_type = 'job_listing'
    AND pm.meta_key = '_postrocket_is_duplicate'
    AND pm.meta_value = 'yes'
");

// Total companies
$companies_query = "
    SELECT COUNT(DISTINCT meta_value) 
    FROM {$wpdb->postmeta} 
    WHERE meta_key = '_company_id' 
    AND meta_value != ''
";
$total_companies = $wpdb->get_var($companies_query);

// Total locations
$locations_query = "
    SELECT COUNT(DISTINCT meta_value) 
    FROM {$wpdb->postmeta} 
    WHERE meta_key = '_job_location' 
    AND meta_value != ''
";
$total_locations = $wpdb->get_var($locations_query);

// Recent jobs
$recent_jobs_query = "
    SELECT p.ID, p.post_title, p.post_date, pm_loc.meta_value as location
    FROM {$wpdb->posts} p
    LEFT JOIN {$wpdb->postmeta} pm_loc ON p.ID = pm_loc.post_id AND pm_loc.meta_key = '_job_location'
    WHERE p.post_type = 'job_listing'
    AND p.post_status = 'publish'
    ORDER BY p.post_date DESC
    LIMIT 10
";
$recent_jobs = $wpdb->get_results($recent_jobs_query);

// Location distribution
$location_distribution_query = "
    SELECT meta_value as location, COUNT(*) as count
    FROM {$wpdb->postmeta}
    WHERE meta_key = '_job_location'
    AND meta_value != ''
    GROUP BY meta_value
    ORDER BY count DESC
    LIMIT 10
";
$location_distribution = $wpdb->get_results($location_distribution_query);

// Prepare chart data
$chart_labels = array();
$chart_data = array();
$chart_colors = array();

foreach ($location_distribution as $location) {
    $chart_labels[] = $location->location;
    $chart_data[] = $location->count;
    
    // Generate random color
    $chart_colors[] = 'rgba(' . rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ', 0.7)';
}

$chart_labels_json = json_encode($chart_labels);
$chart_data_json = json_encode($chart_data);
$chart_colors_json = json_encode($chart_colors);
?>

<div class="wrap postrocket-dashboard">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="postrocket-dashboard-header">
        <div class="postrocket-stats-cards">
            <div class="postrocket-stats-card">
                <div class="postrocket-stats-card-content">
                    <h2><?php echo esc_html($total_jobs); ?></h2>
                    <p>Total Jobs</p>
                </div>
                <div class="postrocket-stats-card-icon">
                    <span class="dashicons dashicons-portfolio"></span>
                </div>
            </div>
            
            <div class="postrocket-stats-card">
                <div class="postrocket-stats-card-content">
                    <h2><?php echo esc_html($active_jobs); ?></h2>
                    <p>Active Jobs</p>
                </div>
                <div class="postrocket-stats-card-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
            </div>
            
            <div class="postrocket-stats-card">
                <div class="postrocket-stats-card-content">
                    <h2><?php echo esc_html($duplicated_jobs); ?></h2>
                    <p>Duplicated Jobs</p>
                </div>
                <div class="postrocket-stats-card-icon">
                    <span class="dashicons dashicons-admin-page"></span>
                </div>
            </div>
            
            <div class="postrocket-stats-card">
                <div class="postrocket-stats-card-content">
                    <h2><?php echo esc_html($total_companies); ?></h2>
                    <p>Total Companies</p>
                </div>
                <div class="postrocket-stats-card-icon">
                    <span class="dashicons dashicons-building"></span>
                </div>
            </div>
            
            <div class="postrocket-stats-card">
                <div class="postrocket-stats-card-content">
                    <h2><?php echo esc_html($total_locations); ?></h2>
                    <p>Total Locations</p>
                </div>
                <div class="postrocket-stats-card-icon">
                    <span class="dashicons dashicons-location"></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="postrocket-dashboard-content">
        <div class="postrocket-dashboard-column">
            <div class="postrocket-card">
                <h2>Recent Jobs</h2>
                <table class="postrocket-table">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Location</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_jobs)) : ?>
                            <tr>
                                <td colspan="3">No jobs found.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($recent_jobs as $job) : ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo esc_url(get_edit_post_link($job->ID)); ?>">
                                            <?php echo esc_html($job->post_title); ?>
                                        </a>
                                    </td>
                                    <td><?php echo esc_html($job->location); ?></td>
                                    <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($job->post_date))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="postrocket-dashboard-column">
            <div class="postrocket-card">
                <h2>Location Distribution</h2>
                <div class="postrocket-chart-container">
                    <canvas id="locationChart"></canvas>
                </div>
                <?php if (empty($location_distribution)) : ?>
                    <p class="postrocket-no-data">No location data available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        <?php if (!empty($location_distribution)) : ?>
        var ctx = document.getElementById('locationChart').getContext('2d');
        var locationChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: <?php echo $chart_labels_json; ?>,
                datasets: [{
                    data: <?php echo $chart_data_json; ?>,
                    backgroundColor: <?php echo $chart_colors_json; ?>,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12
                    }
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var dataset = data.datasets[tooltipItem.datasetIndex];
                            var total = dataset.data.reduce(function(previousValue, currentValue) {
                                return previousValue + currentValue;
                            });
                            var currentValue = dataset.data[tooltipItem.index];
                            var percentage = Math.floor(((currentValue/total) * 100)+0.5);
                            return data.labels[tooltipItem.index] + ': ' + currentValue + ' jobs (' + percentage + '%)';
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    });
</script>