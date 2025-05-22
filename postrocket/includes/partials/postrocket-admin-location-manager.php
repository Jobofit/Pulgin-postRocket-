<?php
/**
 * Admin location manager page partial.
 *
 * @since      1.0.0
 * @package    PostRocket
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Initialize classes
$location_manager = new PostRocket_Location_Manager();

// Get location lists
$location_lists = $location_manager->get_all_location_lists();
?>

<div class="wrap postrocket-location-manager">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="postrocket-card">
        <h2>Create Location List</h2>
        
        <form id="postrocket-location-form" class="postrocket-form">
            <div class="postrocket-form-row">
                <label for="postrocket-list-name">List Name:</label>
                <input type="text" id="postrocket-list-name" name="name" placeholder="Enter a name for this list" required>
                <p class="postrocket-form-help">Choose a descriptive name for your location list.</p>
            </div>
            
            <div class="postrocket-form-row">
                <label for="postrocket-list-locations">Locations:</label>
                <textarea id="postrocket-list-locations" name="locations" rows="8" placeholder="Enter locations separated by commas" required></textarea>
                <p class="postrocket-form-help">
                    Enter locations separated by commas. Maximum 50 locations per list.
                    <span id="postrocket-list-location-count">0</span> locations entered.
                </p>
            </div>
            
            <input type="hidden" id="postrocket-list-id" name="id" value="">
            
            <div class="postrocket-form-actions">
                <button type="submit" class="button button-primary" id="postrocket-save-list">Save List</button>
                <button type="button" class="button" id="postrocket-cancel-edit" style="display: none;">Cancel Edit</button>
                <div class="postrocket-spinner" style="display: none;"></div>
            </div>
        </form>
        
        <div id="postrocket-list-result" class="postrocket-result" style="display: none;"></div>
    </div>
    
    <div class="postrocket-card">
        <h2>Your Location Lists</h2>
        
        <?php if (empty($location_lists)) : ?>
            <p class="postrocket-no-data">No location lists found. Create your first list above.</p>
        <?php else : ?>
            <div class="postrocket-table-responsive">
                <table class="postrocket-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Locations</th>
                            <th>Created</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($location_lists as $list) : ?>
                            <tr>
                                <td><?php echo esc_html($list['name']); ?></td>
                                <td><?php echo esc_html($list['count']); ?> locations</td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($list['created']))); ?></td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($list['updated']))); ?></td>
                                <td>
                                    <button type="button" class="button button-small postrocket-view-list" 
                                            data-id="<?php echo esc_attr($list['id']); ?>">
                                        View
                                    </button>
                                    <button type="button" class="button button-small postrocket-edit-list" 
                                            data-id="<?php echo esc_attr($list['id']); ?>"
                                            data-name="<?php echo esc_attr($list['name']); ?>">
                                        Edit
                                    </button>
                                    <button type="button" class="button button-small postrocket-delete-list" 
                                            data-id="<?php echo esc_attr($list['id']); ?>"
                                            data-name="<?php echo esc_attr($list['name']); ?>">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Location Viewer Modal -->
    <div id="postrocket-location-viewer" class="postrocket-modal" style="display: none;">
        <div class="postrocket-modal-content">
            <span class="postrocket-modal-close">&times;</span>
            <h2 id="postrocket-modal-title">Location List</h2>
            <div id="postrocket-modal-content"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Location counter
        $('#postrocket-list-locations').on('input', function() {
            var text = $(this).val();
            var locations = text.split(',').filter(function(item) {
                return item.trim() !== '';
            });
            $('#postrocket-list-location-count').text(locations.length);
        });
        
        // Form submission
        $('#postrocket-location-form').submit(function(e) {
            e.preventDefault();
            
            // Show spinner
            $('.postrocket-spinner').show();
            $('#postrocket-list-result').hide();
            
            // Get form data
            var formData = {
                'action': 'postrocket_save_location_list',
                'nonce': postrocket_ajax.nonce,
                'name': $('#postrocket-list-name').val(),
                'locations': $('#postrocket-list-locations').val(),
                'id': $('#postrocket-list-id').val()
            };
            
            // Send AJAX request
            $.post(postrocket_ajax.ajax_url, formData, function(response) {
                $('.postrocket-spinner').hide();
                
                if (response.success) {
                    $('#postrocket-list-result').html(
                        '<div class="notice notice-success"><p>Location list saved successfully.</p></div>'
                    ).show();
                    
                    // Reset form
                    $('#postrocket-location-form')[0].reset();
                    $('#postrocket-list-id').val('');
                    $('#postrocket-list-location-count').text('0');
                    $('#postrocket-save-list').text('Save List');
                    $('#postrocket-cancel-edit').hide();
                    
                    // Reload page after a short delay
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    var message = response.data && response.data.message ? response.data.message : 'An error occurred.';
                    $('#postrocket-list-result').html(
                        '<div class="notice notice-error"><p>' + message + '</p></div>'
                    ).show();
                }
            }).fail(function() {
                $('.postrocket-spinner').hide();
                $('#postrocket-list-result').html(
                    '<div class="notice notice-error"><p>Request failed. Please try again.</p></div>'
                ).show();
            });
        });
        
        // View list
        $('.postrocket-view-list').click(function() {
            var id = $(this).data('id');
            
            // Show loading in modal
            $('#postrocket-modal-title').text('Loading...');
            $('#postrocket-modal-content').html('<div class="postrocket-spinner"></div>');
            $('#postrocket-location-viewer').show();
            
            // Get list data
            $.post(postrocket_ajax.ajax_url, {
                'action': 'postrocket_get_location_list',
                'nonce': postrocket_ajax.nonce,
                'id': id
            }, function(response) {
                if (response.success) {
                    var list = response.data;
                    
                    $('#postrocket-modal-title').text(list.name);
                    
                    var content = '<div class="postrocket-locations-list">';
                    
                    if (list.locations.length > 0) {
                        content += '<ul>';
                        $.each(list.locations, function(i, location) {
                            content += '<li>' + location + '</li>';
                        });
                        content += '</ul>';
                    } else {
                        content += '<p>No locations in this list.</p>';
                    }
                    
                    content += '</div>';
                    
                    $('#postrocket-modal-content').html(content);
                } else {
                    var message = response.data && response.data.message ? response.data.message : 'An error occurred.';
                    $('#postrocket-modal-title').text('Error');
                    $('#postrocket-modal-content').html('<p>' + message + '</p>');
                }
            }).fail(function() {
                $('#postrocket-modal-title').text('Error');
                $('#postrocket-modal-content').html('<p>Request failed. Please try again.</p>');
            });
        });
        
        // Edit list
        $('.postrocket-edit-list').click(function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            
            // Get list data
            $.post(postrocket_ajax.ajax_url, {
                'action': 'postrocket_get_location_list',
                'nonce': postrocket_ajax.nonce,
                'id': id
            }, function(response) {
                if (response.success) {
                    var list = response.data;
                    
                    // Fill form
                    $('#postrocket-list-name').val(list.name);
                    $('#postrocket-list-locations').val(list.locations.join(', '));
                    $('#postrocket-list-id').val(list.id);
                    $('#postrocket-list-location-count').text(list.locations.length);
                    
                    // Update UI
                    $('#postrocket-save-list').text('Update List');
                    $('#postrocket-cancel-edit').show();
                    
                    // Scroll to form
                    $('html, body').animate({
                        scrollTop: $('#postrocket-location-form').offset().top - 50
                    }, 500);
                } else {
                    var message = response.data && response.data.message ? response.data.message : 'An error occurred.';
                    alert('Error: ' + message);
                }
            }).fail(function() {
                alert('Request failed. Please try again.');
            });
        });
        
        // Cancel edit
        $('#postrocket-cancel-edit').click(function() {
            // Reset form
            $('#postrocket-location-form')[0].reset();
            $('#postrocket-list-id').val('');
            $('#postrocket-list-location-count').text('0');
            $('#postrocket-save-list').text('Save List');
            $(this).hide();
        });
        
        // Delete list
        $('.postrocket-delete-list').click(function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            
            if (!confirm('Are you sure you want to delete the location list "' + name + '"? This cannot be undone.')) {
                return;
            }
            
            // Send delete request
            $.post(postrocket_ajax.ajax_url, {
                'action': 'postrocket_delete_location_list',
                'nonce': postrocket_ajax.nonce,
                'id': id
            }, function(response) {
                if (response.success) {
                    alert('Location list deleted successfully.');
                    location.reload();
                } else {
                    var message = response.data && response.data.message ? response.data.message : 'An error occurred.';
                    alert('Error: ' + message);
                }
            }).fail(function() {
                alert('Request failed. Please try again.');
            });
        });
        
        // Modal close
        $('.postrocket-modal-close').click(function() {
            $('.postrocket-modal').hide();
        });
        
        // Close modal when clicking outside
        $(window).click(function(event) {
            if ($(event.target).hasClass('postrocket-modal')) {
                $('.postrocket-modal').hide();
            }
        });
    });
</script>