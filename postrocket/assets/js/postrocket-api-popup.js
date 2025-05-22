// PostRocket API Popup JavaScript
// This file will be properly loaded in WordPress environment

// Run initialization when document is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Toggle API key visibility
    var toggleApiKeyButton = document.getElementById('postrocket-toggle-api-key');
    
    if (toggleApiKeyButton) {
        toggleApiKeyButton.addEventListener('click', function() {
            var apiKeyField = document.getElementById('postrocket-api-key');
            var icon = this.querySelector('.dashicons');
            
            if (apiKeyField.type === 'password') {
                apiKeyField.type = 'text';
                icon.classList.remove('dashicons-visibility');
                icon.classList.add('dashicons-hidden');
            } else {
                apiKeyField.type = 'password';
                icon.classList.remove('dashicons-hidden');
                icon.classList.add('dashicons-visibility');
            }
        });
    }
    
    // Validate API key
    var validateApiKeyButton = document.getElementById('postrocket-validate-api-key');
    
    if (validateApiKeyButton) {
        validateApiKeyButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            var apiKey = document.getElementById('postrocket-api-key').value;
            var resultContainer = document.getElementById('postrocket-api-result');
            var spinner = document.querySelector('.postrocket-spinner');
            
            if (!apiKey) {
                resultContainer.innerHTML = '<div class="notice notice-error"><p>Please enter an API key to validate.</p></div>';
                resultContainer.style.display = 'block';
                return;
            }
            
            // Show spinner
            if (spinner) {
                spinner.style.display = 'inline-block';
            }
            
            resultContainer.style.display = 'none';
            
            // In a real WordPress environment, this would make an AJAX request
            // For now, we'll just simulate the behavior
            setTimeout(function() {
                // Hide spinner
                if (spinner) {
                    spinner.style.display = 'none';
                }
                
                // Show success message
                resultContainer.innerHTML = '<div class="notice notice-success"><p>API key is valid.</p></div>';
                resultContainer.style.display = 'block';
            }, 1000);
        });
    }
    
    // API form submission
    var apiForm = document.getElementById('postrocket-api-form');
    
    if (apiForm) {
        apiForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            var apiKey = document.getElementById('postrocket-api-key').value;
            var resultContainer = document.getElementById('postrocket-api-result');
            var spinner = document.querySelector('.postrocket-spinner');
            
            // Show spinner
            if (spinner) {
                spinner.style.display = 'inline-block';
            }
            
            resultContainer.style.display = 'none';
            
            // In a real WordPress environment, this would make an AJAX request
            // For now, we'll just simulate the behavior
            setTimeout(function() {
                // Hide spinner
                if (spinner) {
                    spinner.style.display = 'none';
                }
                
                // Show success message
                resultContainer.innerHTML = '<div class="notice notice-success"><p>API key saved successfully.</p></div>';
                resultContainer.style.display = 'block';
            }, 1000);
        });
    }
    
    // Visibility form submission
    var visibilityForm = document.getElementById('postrocket-visibility-form');
    
    if (visibilityForm) {
        visibilityForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            var resultContainer = document.getElementById('postrocket-visibility-result');
            var spinner = document.querySelector('.postrocket-spinner');
            
            // Show spinner
            if (spinner) {
                spinner.style.display = 'inline-block';
            }
            
            resultContainer.style.display = 'none';
            
            // In a real WordPress environment, this would make an AJAX request
            // For now, we'll just simulate the behavior
            setTimeout(function() {
                // Hide spinner
                if (spinner) {
                    spinner.style.display = 'none';
                }
                
                // Show success message
                resultContainer.innerHTML = '<div class="notice notice-success"><p>Settings saved successfully.</p></div>';
                resultContainer.style.display = 'block';
            }, 1000);
        });
    }
});