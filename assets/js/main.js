/**
 * Oktoberfest VIP Booking System
 * Main JavaScript file
 */

(function($) {
    'use strict';
    
    /**
     * VIP Pass Widget (Widget 1)
     */
    const VIPPassWidget = {
        init: function() {
            this.dateInput = $('#booking_date');
            this.locationSelect = $('#tent');
            this.form = $('#reservation-form');
            
            if (this.form.length) {
                this.initDatePicker();
                this.bindEvents();
            }
        },
        
        initDatePicker: function() {
            // Check if flatpickr is available (optional enhancement)
            if (typeof flatpickr !== 'undefined') {
                // Get date range from settings
                const startDate = this.form.data('start-date') || null;
                const endDate = this.form.data('end-date') || null;
                
                flatpickr(this.dateInput[0], {
                    dateFormat: 'Y-m-d',
                    minDate: startDate,
                    maxDate: endDate,
                    disable: [
                        function(date) {
                            // Disable weekends if needed
                            // return date.getDay() === 0 || date.getDay() === 6;
                            return false;
                        }
                    ]
                });
            }
        },
        
        bindEvents: function() {
            this.form.on('submit', this.handleSubmit.bind(this));
        },
        
        handleSubmit: function(e) {
            e.preventDefault();
            
            // Validate form
            if (!this.validateForm()) {
                return;
            }
            
            // Get form values
            const date = this.dateInput.val();
            const location = this.locationSelect.val();
            
            // Base64 encode the values
            const encodedDate = btoa(date);
            const encodedLocation = btoa(location);
            
            // Get redirect URL
            const redirectUrl = this.form.attr('action');
            
            // Construct full URL with parameters
            const bookingUrl = redirectUrl + '?date=' + encodedDate + '&location=' + encodedLocation;
            
            // Redirect to booking form page
            window.location.href = bookingUrl;
        },
        
        validateForm: function() {
            let isValid = true;
            
            // Date validation
            if (!this.dateInput.val()) {
                this.showError(this.dateInput, 'Please select a date');
                isValid = false;
            } else {
                this.clearError(this.dateInput);
            }
            
            // Location validation
            if (!this.locationSelect.val()) {
                this.showError(this.locationSelect, 'Please select a location');
                isValid = false;
            } else {
                this.clearError(this.locationSelect);
            }
            
            return isValid;
        },
        
        showError: function(element, message) {
            const errorElement = $('<div class="error-message">' + message + '</div>');
            element.addClass('error');
            
            // Remove existing error message if any
            element.siblings('.error-message').remove();
            
            // Add new error message
            element.after(errorElement);
        },
        
        clearError: function(element) {
            element.removeClass('error');
            element.siblings('.error-message').remove();
        }
    };
    
    /**
     * Booking Form Widget (Widget 2)
     */
    const BookingFormWidget = {
        init: function() {
            this.form = $('#vip-booking-form');
            this.tentGallery = $('#tent-gallery');
            this.tentCards = $('.tent-card');
            this.tentPreferenceRadios = $('input[name="tent_preference"]');
            this.selectedTentInput = $('#selected-tent');
            
            if (this.form.length) {
                this.bindEvents();
                this.initCalendar();
            }
        },
        
        bindEvents: function() {
            this.form.on('submit', this.handleSubmit.bind(this));
            
            // Tent preference selection
            this.tentPreferenceRadios.on('change', this.handleTentPreferenceChange.bind(this));
            
            // Tent card selection
            this.tentCards.on('click', this.handleTentCardClick.bind(this));
        },
        
        initCalendar: function() {
            const selectedDate = $('input[name="selected_date"]').val();
            
            // This would be expanded to include a full calendar implementation
            // For now, we'll just highlight the selected date in the calendar
            if (selectedDate) {
                const day = new Date(selectedDate).getDate();
                $('.calendar-dates div').each(function() {
                    if (parseInt($(this).text()) === day) {
                        $(this).addClass('selected');
                    }
                });
            }
        },
        
        handleTentPreferenceChange: function() {
            const preference = $('input[name="tent_preference"]:checked').val();
            
            if (preference === 'specific') {
                this.tentGallery.slideDown();
            } else {
                this.tentGallery.slideUp();
                this.selectedTentInput.val('any');
                this.tentCards.removeClass('selected');
            }
        },
        
        handleTentCardClick: function(e) {
            const tentCard = $(e.currentTarget);
            const tentId = tentCard.data('tent-id');
            
            // Ensure specific tent preference is selected
            this.tentPreferenceRadios.filter('[value="specific"]').prop('checked', true);
            
            // Update selected tent
            this.tentCards.removeClass('selected');
            tentCard.addClass('selected');
            this.selectedTentInput.val(tentId);
            
            // Show tent gallery if it was hidden
            this.tentGallery.slideDown();
        },
        
        handleSubmit: function(e) {
            e.preventDefault();
            
            // Validate form
            if (!this.validateForm()) {
                return;
            }
            
            // Get form data
            const formData = new FormData(this.form[0]);
            formData.append('action', 'oktoberfest_vip_submit_booking');
            formData.append('nonce', OktoberfestVIP.nonce);
            
            // Show loading state
            this.setFormLoading(true);
            
            // Submit the form
            $.ajax({
                url: OktoberfestVIP.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: this.handleSubmitSuccess.bind(this),
                error: this.handleSubmitError.bind(this)
            });
        },
        
        validateForm: function() {
            let isValid = true;
            
            // Validate required fields
            this.form.find('[required]').each(function() {
                const field = $(this);
                
                if (!field.val()) {
                    BookingFormWidget.showError(field, 'This field is required');
                    isValid = false;
                } else {
                    BookingFormWidget.clearError(field);
                }
            });
            
            // Validate email format
            const emailField = this.form.find('input[type="email"]');
            if (emailField.val() && !this.isValidEmail(emailField.val())) {
                this.showError(emailField, 'Please enter a valid email address');
                isValid = false;
            }
            
            // Validate tent selection if specific preference is chosen
            if (this.tentPreferenceRadios.filter(':checked').val() === 'specific' && !this.selectedTentInput.val()) {
                this.showError(this.tentGallery, 'Please select a tent');
                isValid = false;
            } else {
                this.clearError(this.tentGallery);
            }
            
            return isValid;
        },
        
        isValidEmail: function(email) {
            const regex = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
            return regex.test(email);
        },
        
        handleSubmitSuccess: function(response) {
            this.setFormLoading(false);
            
            if (response.success) {
                // Get thank you page URL from form data attribute
                const thankYouPage = this.form.data('thank-you-page') || '/thank-you/';
                
                // Redirect to thank you page
                window.location.href = thankYouPage;
            } else {
                // Show error message
                this.showFormError(response.data.message || 'An error occurred. Please try again.');
            }
        },
        
        handleSubmitError: function() {
            this.setFormLoading(false);
            this.showFormError('Unable to process your request. Please try again later.');
        },
        
        setFormLoading: function(isLoading) {
            const submitButton = this.form.find('button[type="submit"]');
            
            if (isLoading) {
                submitButton.prop('disabled', true).addClass('loading');
                submitButton.html('<span class="spinner"></span> Processing...');
            } else {
                submitButton.prop('disabled', false).removeClass('loading');
                submitButton.text(submitButton.data('original-text') || 'Submit Inquiry');
            }
        },
        
        showFormError: function(message) {
            // Remove existing error message
            this.form.find('.form-error').remove();
            
            // Add new error message
            const errorMessage = $('<div class="form-error">' + message + '</div>');
            this.form.prepend(errorMessage);
            
            // Scroll to error message
            $('html, body').animate({
                scrollTop: this.form.offset().top - 100
            }, 500);
        },
        
        showError: function(element, message) {
            const errorElement = $('<div class="error-message">' + message + '</div>');
            element.addClass('error');
            
            // Remove existing error message if any
            element.siblings('.error-message').remove();
            
            // Add new error message
            element.after(errorElement);
        },
        
        clearError: function(element) {
            element.removeClass('error');
            element.siblings('.error-message').remove();
        }
    };
    
    // Initialize when document is ready
    $(function() {
        VIPPassWidget.init();
        BookingFormWidget.init();
    });
    
})(jQuery);