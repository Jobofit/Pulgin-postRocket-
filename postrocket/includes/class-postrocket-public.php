<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 * @package    PostRocket
 */

class PostRocket_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version           The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Add noindex meta tags to duplicated jobs.
     *
     * @since    1.0.0
     */
    public function add_noindex_tags() {
        global $post;
        
        if (!is_singular('job_listing')) {
            return;
        }
        
        $is_duplicate = get_post_meta($post->ID, '_postrocket_is_duplicate', true);
        
        if ($is_duplicate === 'yes') {
            echo '<meta name="robots" content="noindex, nofollow" />';
        }
    }

    /**
     * Hide duplicate jobs from queries.
     *
     * @since    1.0.0
     * @param    WP_Query    $query    The WP_Query instance.
     */
    public function hide_duplicate_jobs($query) {
        if (is_admin() || !$query->is_main_query()) {
            return;
        }
        
        if ($query->is_post_type_archive('job_listing') || $query->is_tax('job_listing_category') || $query->is_tax('job_listing_type')) {
            // Add meta query to exclude duplicated jobs
            $meta_query = $query->get('meta_query');
            if (!is_array($meta_query)) {
                $meta_query = array();
            }
            
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key' => '_postrocket_is_duplicate',
                    'compare' => 'NOT EXISTS'
                ),
                array(
                    'key' => '_postrocket_is_duplicate',
                    'value' => 'yes',
                    'compare' => '!='
                )
            );
            
            $query->set('meta_query', $meta_query);
        }
    }
}