// PostRocket Background Processing JavaScript
// This file will be properly loaded in WordPress environment

// Poll queue status
function postrocket_poll_queue_status() {
    setTimeout(function() {
        // In WordPress environment, this would use the global postrocket_ajax object
        // We'll keep this as a placeholder for the WordPress implementation
        var ajax_url = '/wp-admin/admin-ajax.php';
        var nonce = 'placeholder';
        
        var xhr = new XMLHttpRequest();
        xhr.open('POST', ajax_url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    
                    if (response.success) {
                        var status = response.data;
                        
                        if (status.is_processing) {
                            // Continue polling
                            postrocket_poll_queue_status();
                        } else {
                            // Refresh page
                            window.location.reload();
                        }
                    } else {
                        // Stop polling on error
                        console.error('Error polling queue status');
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                }
            }
        };
        
        var data = 'action=postrocket_get_queue_status&nonce=' + nonce;
        xhr.send(data);
    }, 5000); // Poll every 5 seconds
}

// Run initialization when document is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Process queue button
    var processQueueButton = document.getElementById('postrocket-process-queue');
    
    if (processQueueButton) {
        processQueueButton.addEventListener('click', function() {
            // Show spinner
            var spinner = document.querySelector('.postrocket-spinner');
            if (spinner) {
                spinner.style.display = 'inline-block';
            }
            
            // Disable button
            this.disabled = true;
            
            // In a real WordPress environment, this would make an AJAX request
            // For now, we'll just simulate the behavior
            setTimeout(function() {
                // Start polling for updates
                postrocket_poll_queue_status();
            }, 1000);
        });
    }
    
    // Refresh status button
    var refreshStatusButton = document.getElementById('postrocket-refresh-status');
    
    if (refreshStatusButton) {
        refreshStatusButton.addEventListener('click', function() {
            // Show spinner
            var spinner = document.querySelector('.postrocket-spinner');
            if (spinner) {
                spinner.style.display = 'inline-block';
            }
            
            // Disable button
            this.disabled = true;
            
            // Refresh page
            window.location.reload();
        });
    }
});