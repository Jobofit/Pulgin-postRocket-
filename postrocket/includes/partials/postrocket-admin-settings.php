<?php
/**
 * Admin settings page partial.
 *
 * @since      1.0.0
 * @package    PostRocket
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Initialize classes
$api = new PostRocket_API();

// Get current settings
$api_key = $api->get_api_key();
$hide_duplicates = get_option('postrocket_hide_duplicates', 'no');
$noindex_duplicates = get_option('postrocket_noindex_duplicates', 'no');
?>

<div class="wrap postrocket-settings">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="postrocket-card">
        <h2>API Settings</h2>
        
        <form id="postrocket-api-form" class="postrocket-form">
            <div class="postrocket-form-row">
                <label for="postrocket-api-key">API Key:</label>
                <input type="password" id="postrocket-api-key" name="api_key" 
                       value="<?php echo esc_attr($api_key); ?>" placeholder="Enter your API key">
                <button type="button" id="postrocket-toggle-api-key" class="button button-small">
                    <span class="dashicons dashicons-visibility"></span>
                </button>
                <p class="postrocket-form-help">
                    Enter your PostRocket API key. This is required for job duplication functionality.
                    <a href="#" id="postrocket-validate-api-key">Validate Key</a>
                </p>
            </div>
            
            <div class="postrocket-form-actions">
                <button type="submit" class="button button-primary">Save API Key</button>
                <div class="postrocket-spinner" style="display: none;"></div>
            </div>
        </form>
        
        <div id="postrocket-api-result" class="postrocket-result" style="display: none;"></div>
    </div>
    
    <div class="postrocket-card">
        <h2>Visibility Settings</h2>
        
        <form id="postrocket-visibility-form" class="postrocket-form">
            <div class="postrocket-form-row">
                <label>Hide Duplicated Jobs:</label>
                <div class="postrocket-radio-group">
                    <label>
                        <input type="radio" name="hide_duplicates" value="yes" <?php checked($hide_duplicates, 'yes'); ?>> 
                        Yes
                    </label>
                    <label>
                        <input type="radio" name="hide_duplicates" value="no" <?php checked($hide_duplicates, 'no'); ?>> 
                        No
                    </label>
                </div>
                <p class="postrocket-form-help">
                    If enabled, duplicated jobs will be hidden from frontend job listings.
                </p>
            </div>
            
            <div class="postrocket-form-row">
                <label>Add Noindex Tags:</label>
                <div class="postrocket-radio-group">
                    <label>
                        <input type="radio" name="noindex_duplicates" value="yes" <?php checked($noindex_duplicates, 'yes'); ?>> 
                        Yes
                    </label>
                    <label>
                        <input type="radio" name="noindex_duplicates" value="no" <?php checked($noindex_duplicates, 'no'); ?>> 
                        No
                    </label>
                </div>
                <p class="postrocket-form-help">
                    If enabled, noindex meta tags will be added to duplicated jobs to prevent search engine indexing.
                </p>
            </div>
            
            <div class="postrocket-form-actions">
                <button type="submit" class="button button-primary">Save Settings</button>
                <div class="postrocket-spinner" style="display: none;"></div>
            </div>
        </form>
        
        <div id="postrocket-visibility-result" class="postrocket-result" style="display: none;"></div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Toggle API key visibility
        $('#postrocket-toggle-api-key').click(function() {
            var apiKeyField = $('#postrocket-api-key');
            var icon = $(this).find('.dashicons');
            
            if (apiKeyField.attr('type') === 'password') {
                apiKeyField.attr('type', 'text');
                icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
            } else {
                apiKeyField.attr('type', 'password');
                icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
            }
        });
        
        // Validate API key
        $('#postrocket-validate-api-key').click(function(e) {
            e.preventDefault();
            
            var apiKey = $('#postrocket-api-key').val();
            
            if (!apiKey) {
                $('#postrocket-api-result').html(
                    '<div class="notice notice-error"><p>Please enter an API key to validate.</p></div>'
                ).show();
                return;
            }
            
            // Show spinner
            $('.postrocket-spinner').show();
            $('#postrocket-api-result').hide();
            
            // Send AJAX request
            $.post(postrocket_ajax.ajax_url, {
                'action': 'postrocket_validate_api_key',
                'nonce': postrocket_ajax.nonce,
                'api_key': apiKey
            }, function(response) {
                $('.postrocket-spinner').hide();
                
                if (response.success) {
                    $('#postrocket-api-result').html(
                        '<div class="notice notice-success"><p>API key is valid.</p></div>'
                    ).show();
                } else {
                    var message = response.data && response.data.message ? response.data.message : 'API key is invalid.';
                    $('#postrocket-api-result').html(
                        '<div class="notice notice-error"><p>' + message + '</p></div>'
                    ).show();
                }
            }).fail(function() {
                $('.postrocket-spinner').hide();
                $('#postrocket-api-result').html(
                    '<div class="notice notice-error"><p>Request failed. Please try again.</p></div>'
                ).show();
            });
        });
        
        // Save API key
        $('#postrocket-api-form').submit(function(e) {
            e.preventDefault();
            
            // Show spinner
            $('.postrocket-spinner').show();
            $('#postrocket-api-result').hide();
            
            // Get API key
            var apiKey = $('#postrocket-api-key').val();
            
            // Send AJAX request
            $.post(postrocket_ajax.ajax_url, {
                'action': 'postrocket_save_api_key',
                'nonce': postrocket_ajax.nonce,
                'api_key': apiKey
            }, function(response) {
                $('.postrocket-spinner').hide();
                
                if (response.success) {
                    $('#postrocket-api-result').html(
                        '<div class="notice notice-success"><p>API key saved successfully.</p></div>'
                    ).show();
                } else {
                    var message = response.data && response.data.message ? response.data.message : 'Failed to save API key.';
                    $('#postrocket-api-result').html(
                        '<div class="notice notice-error"><p>' + message + '</p></div>'
                    ).show();
                }
            }).fail(function() {
                $('.postrocket-spinner').hide();
                $('#postrocket-api-result').html(
                    '<div class="notice notice-error"><p>Request failed. Please try again.</p></div>'
                ).show();
            });
        });
        
        // Save visibility settings
        $('#postrocket-visibility-form').submit(function(e) {
            e.preventDefault();
            
            // Show spinner
            $('.postrocket-spinner').show();
            $('#postrocket-visibility-result').hide();
            
            // Get form data
            var formData = {
                'action': 'postrocket_save_settings',
                'nonce': postrocket_ajax.nonce,
                'hide_duplicates': $('input[name="hide_duplicates"]:checked').val(),
                'noindex_duplicates': $('input[name="noindex_duplicates"]:checked').val()
            };
            
            // Send AJAX request
            $.post(postrocket_ajax.ajax_url, formData, function(response) {
                $('.postrocket-spinner').hide();
                
                if (response.success) {
                    $('#postrocket-visibility-result').html(
                        '<div class="notice notice-success"><p>Settings saved successfully.</p></div>'
                    ).show();
                } else {
                    var message = response.data && response.data.message ? response.data.message : 'Failed to save settings.';
                    $('#postrocket-visibility-result').html(
                        '<div class="notice notice-error"><p>' + message + '</p></div>'
                    ).show();
                }
            }).fail(function() {
                $('.postrocket-spinner').hide();
                $('#postrocket-visibility-result').html(
                    '<div class="notice notice-error"><p>Request failed. Please try again.</p></div>'
                ).show();
            });
        });
    });
</script>