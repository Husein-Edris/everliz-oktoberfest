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
        init() {
            this.cacheDom();
            if (!this.form.length) return;
            this.bindEvents();
            this.initCalendar();
            this.updateSummary();
        },
        cacheDom() {
            this.form = $('#vip-booking-form');
            this.tentGallery = $('#tent-gallery');
            this.tentCards = $('.tent-card');
            this.tentPreferenceRadios = $('input[name="tent_preference"]');
            this.selectedTentInput = $('#selected-tent');
            this.selectedDateInput = $('input[name="selected_date"]');
            this.summary = $('#booking-summary');
        },
        bindEvents() {
            this.form.on('submit', e => this.handleSubmit(e));
            $('.preference-option').on('click', e => {
                if (!$(e.target).is('input[type="radio"]')) {
                    $(e.currentTarget).find('input[type="radio"]').prop('checked', true).trigger('change');
                }
            });
            this.tentPreferenceRadios.on('change', e => this.handlePreferenceChange(e));
            this.tentCards.on('click', e => this.handleTentCardClick(e));
            this.selectedDateInput.on('change', () => this.updateUrlAndSummary());
        },
        handlePreferenceChange(e) {
            this.tentPreferenceRadios.closest('.preference-option').removeClass('selected');
            this.tentPreferenceRadios.closest('.radio-col').removeClass('selected');
            $(e.target).closest('.preference-option').addClass('selected');
            $(e.target).closest('.radio-col').addClass('selected');
            this.handleTentGallery();
            this.updateUrlAndSummary();
        },
        handleTentGallery() {
            const pref = this.tentPreferenceRadios.filter(':checked').val();
            if (pref === 'specific') {
                this.tentGallery.slideDown();
            } else {
                this.tentGallery.slideUp();
                this.selectedTentInput.val('any');
                this.tentCards.removeClass('selected');
            }
        },
        handleTentCardClick(e) {
            const tentCard = $(e.currentTarget);
            const tentId = tentCard.data('tent-id');
            this.tentPreferenceRadios.filter('[value="specific"]').prop('checked', true).trigger('change');
            this.tentCards.removeClass('selected');
            tentCard.addClass('selected');
            this.selectedTentInput.val(tentId);
            this.tentGallery.slideDown();
            this.updateUrlAndSummary();
        },
        updateUrlAndSummary() {
            this.updateUrl();
            this.updateSummary();
        },
        updateUrl() {
            const date = this.selectedDateInput.val();
            const tent = this.selectedTentInput.val();
            const params = new URLSearchParams(window.location.search);
            if (date) params.set('date', btoa(date));
            if (tent) params.set('location', btoa(tent));
            history.replaceState(null, '', '?' + params.toString());
        },
        updateSummary() {
            if (!this.summary.length) return;
            const tentId = this.selectedTentInput.val();
            const date = this.selectedDateInput.val();
            let tentName = '', tentImg = '';
            if (window.EverlizTents && tentId && tentId !== 'any') {
                const tent = window.EverlizTents.find(t => t.id === tentId);
                if (tent) {
                    tentName = tent.name;
                    tentImg = tent.image;
                }
            } else if (tentId === 'any') {
                tentName = 'Any Tent';
            }
            let dateStr = '';
            if (date) {
                const d = new Date(date);
                dateStr = d.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            }
            let html = tentImg ? `<img src="${tentImg}" alt="${tentName}" style="max-width:60px;max-height:60px;border-radius:8px;margin-right:1em;vertical-align:middle;">` : '';
            html += `<strong>${tentName}</strong>`;
            if (dateStr) html += ` <span style="color:#aaa;">on</span> <strong>${dateStr}</strong>`;
            this.summary.html(html);
        },
        handleSubmit(e) {
            e.preventDefault();
            if (!this.validateForm()) return;
            const formData = new FormData(this.form[0]);
            formData.append('action', 'oktoberfest_vip_submit_booking');
            formData.append('nonce', OktoberfestVIP.nonce);
            this.setFormLoading(true);
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
        validateForm() {
            let isValid = true;
            this.form.find('[required]').each(function() {
                const field = $(this);
                if (!field.val()) {
                    BookingFormWidget.showError(field, 'This field is required');
                    isValid = false;
                } else {
                    BookingFormWidget.clearError(field);
                }
            });
            const emailField = this.form.find('input[type="email"]');
            if (emailField.val() && !this.isValidEmail(emailField.val())) {
                this.showError(emailField, 'Please enter a valid email address');
                isValid = false;
            }
            if (this.tentPreferenceRadios.filter(':checked').val() === 'specific' && !this.selectedTentInput.val()) {
                this.showError(this.tentGallery, 'Please select a tent');
                isValid = false;
            } else {
                this.clearError(this.tentGallery);
            }
            return isValid;
        },
        isValidEmail(email) {
            const regex = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
            return regex.test(email);
        },
        setFormLoading(isLoading) {
            const submitButton = this.form.find('button[type="submit"]');
            if (isLoading) {
                submitButton.prop('disabled', true).addClass('loading');
                submitButton.html('<span class="spinner"></span> Processing...');
            } else {
                submitButton.prop('disabled', false).removeClass('loading');
                submitButton.text(submitButton.data('original-text') || 'Submit Inquiry');
            }
        },
        showError(element, message) {
            const errorElement = $('<div class="error-message">' + message + '</div>');
            element.addClass('error');
            element.siblings('.error-message').remove();
            element.after(errorElement);
        },
        clearError(element) {
            element.removeClass('error');
            element.siblings('.error-message').remove();
        },
        handleSubmitSuccess(response) {
            this.setFormLoading(false);
            if (response.success) {
                const thankYouPage = this.form.data('thank-you-page') || '/thank-you/';
                window.location.href = thankYouPage;
            } else {
                this.showFormError(response.data.message || 'An error occurred. Please try again.');
            }
        },
        handleSubmitError() {
            this.setFormLoading(false);
            this.showFormError('Unable to process your request. Please try again later.');
        },
        showFormError(message) {
            this.form.find('.form-error').remove();
            const errorMessage = $('<div class="form-error">' + message + '</div>');
            this.form.prepend(errorMessage);
            $('html, body').animate({ scrollTop: this.form.offset().top - 100 }, 500);
        },
        initCalendar() {
            const selectedDate = this.selectedDateInput.val();
            if (selectedDate) {
                const day = new Date(selectedDate).getDate();
                $('.calendar-dates div').each(function() {
                    if (parseInt($(this).text()) === day) {
                        $(this).addClass('selected');
                    }
                });
            }
        }
    };
    
    // Initialize when document is ready
    $(function() {
        VIPPassWidget.init();
        BookingFormWidget.init();
    });
    
})(jQuery);