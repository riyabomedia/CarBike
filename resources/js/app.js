import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// Global JavaScript for your vehicle marketplace
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips if Bootstrap is loaded
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    }
}); 