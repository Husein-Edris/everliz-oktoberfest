// assets/js/booking-form.js
jQuery(document).ready(function($) {
    const form = $('#booking-form');
    
    form.on('submit', function(e) {
        e.preventDefault();
        
        const location = $('#location').val();
        const date = $('#booking_date').val();
        
        // Add form data to URL parameters
        const baseUrl = form.attr('action');
        const url = new URL(baseUrl);
        url.searchParams.set('location', location);
        url.searchParams.set('date', date);
        
        // Redirect to results page
        window.location.href = url.toString();
    });
});