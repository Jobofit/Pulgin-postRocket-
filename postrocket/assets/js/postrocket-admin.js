// PostRocket Admin JavaScript
// This file will be properly loaded in WordPress environment

// Main initialization function
function postrocket_init() {
    // Initialize tooltips
    document.querySelectorAll('.postrocket-tooltip').forEach(function(element) {
        element.addEventListener('mouseenter', function() {
            var tooltip = this.dataset.tooltip;
            
            if (!tooltip) {
                return;
            }
            
            // Create tooltip element
            var tooltipElement = document.createElement('div');
            tooltipElement.className = 'postrocket-tooltip-content';
            tooltipElement.textContent = tooltip;
            
            // Append to body
            document.body.appendChild(tooltipElement);
            
            // Position tooltip
            var rect = this.getBoundingClientRect();
            tooltipElement.style.top = (rect.top - tooltipElement.offsetHeight - 10) + 'px';
            tooltipElement.style.left = (rect.left - (tooltipElement.offsetWidth / 2) + (this.offsetWidth / 2)) + 'px';
            
            // Show tooltip
            tooltipElement.style.opacity = '1';
            
            // Store tooltip element reference
            this.tooltipElement = tooltipElement;
        });
        
        element.addEventListener('mouseleave', function() {
            if (this.tooltipElement) {
                document.body.removeChild(this.tooltipElement);
                this.tooltipElement = null;
            }
        });
    });
    
    // Initialize notifications container
    if (!document.querySelector('.postrocket-toast-container')) {
        var container = document.createElement('div');
        container.className = 'postrocket-toast-container';
        document.body.appendChild(container);
    }
}

// Run initialization when document is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', postrocket_init);
} else {
    postrocket_init();
}