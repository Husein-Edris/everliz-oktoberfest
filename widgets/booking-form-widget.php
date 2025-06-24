<?php

namespace Everliz_Oktoberfest;

if (!defined('ABSPATH')) exit;

use Everliz_Oktoberfest\API_Handler;

class Booking_Form_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'everliz_oktoberfest_booking_form';
    }

    public function get_title()
    {
        return __('Oktoberfest VIP Booking Form', 'everliz-oktoberfest');
    }

    public function get_icon()
    {
        return 'eicon-form-horizontal';
    }

    public function get_categories()
    {
        return ['basic'];
    }

    protected function register_controls()
    {
        // Your existing controls here
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        // Get URL parameters
        $encoded_date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : '';
        $encoded_location = isset($_GET['location']) ? sanitize_text_field($_GET['location']) : '';

        // Decode parameters
        $date = '';
        $location = '';

        if (!empty($encoded_date)) {
            // Try to decode base64, if it fails use as-is
            $decoded_date = base64_decode($encoded_date, true);
            $date = ($decoded_date !== false) ? $decoded_date : $encoded_date;

            // Ensure date is in correct format (YYYY-MM-DD)
            if (strtotime($date)) {
                $date = date('Y-m-d', strtotime($date));
            }
        }

        if (!empty($encoded_location)) {
            // Try to decode base64, if it fails use as-is
            $decoded_location = base64_decode($encoded_location, true);
            $location = ($decoded_location !== false) ? $decoded_location : $encoded_location;
        }

        // Format date for display
        $display_date = !empty($date) ? date('F j, Y', strtotime($date)) : '';

        // Default tent preference
        $tent_preference = !empty($location) && $location !== 'any' ? 'specific' : 'any';

        // Get tents and seasons from API or fallback
        $api_handler = API_Handler::instance();
        $tents = $api_handler->get_tents();
        $date_ranges = $api_handler->get_seasons();
        if (!is_array($date_ranges)) {
            $date_ranges = [[
                'year' => '2025',
                'start_date' => '2025-09-20',
                'end_date' => '2025-10-05'
            ]];
        }
        $first_range = is_array($date_ranges) && !empty($date_ranges) ? reset($date_ranges) : [
            'year' => '2025',
            'start_date' => '2025-09-20',
            'end_date' => '2025-10-05'
        ];
        $start_date = isset($first_range['start_date']) ? $first_range['start_date'] : '2025-09-20';
        $end_date = isset($first_range['end_date']) ? $first_range['end_date'] : '2025-10-05';
        $calendar_date_ranges = [];
        if (is_array($date_ranges)) {
            foreach ($date_ranges as $range) {
                if (isset($range['year'], $range['start_date'], $range['end_date'])) {
                    $calendar_date_ranges[$range['year']] = [
                        'start' => $range['start_date'],
                        'end' => $range['end_date']
                    ];
                }
            }
        }
?>
        <div class="booking-form-container">
            <script>
                window.EverlizTents = <?php echo json_encode($tents); ?>;
            </script>
            <div class="calendar-section">
                <div class="calendar-wrapper" id="oktoberfest-calendar">
                    <!-- Calendar -->
                </div>
                <input type="hidden" name="selected_date" value="<?php echo esc_attr($date); ?>">
            </div>
            <script>
                jQuery(document).ready(function($) {
                    // Initialize calendar if OktoberfestCalendar is available
                    if (typeof OktoberfestCalendar !== 'undefined') {
                        // Get date ranges from WordPress options with proper type checking
                        <?php
                        $date_ranges = get_option('oktoberfest_date_ranges');
                        if (!is_array($date_ranges)) {
                            $date_ranges = [[
                                'year' => '2025',
                                'start_date' => '2025-09-20',
                                'end_date' => '2025-10-05'
                            ]];
                        }

                        // Get default start and end dates from the first available range
                        $first_range = is_array($date_ranges) && !empty($date_ranges) ? reset($date_ranges) : [
                            'year' => '2025',
                            'start_date' => '2025-09-20',
                            'end_date' => '2025-10-05'
                        ];

                        $start_date = isset($first_range['start_date']) ? $first_range['start_date'] : '2025-09-20';
                        $end_date = isset($first_range['end_date']) ? $first_range['end_date'] : '2025-10-05';

                        // Convert date ranges to format expected by calendar
                        $calendar_date_ranges = [];
                        if (is_array($date_ranges)) {
                            foreach ($date_ranges as $range) {
                                if (isset($range['year'], $range['start_date'], $range['end_date'])) {
                                    $calendar_date_ranges[$range['year']] = [
                                        'start' => $range['start_date'],
                                        'end' => $range['end_date']
                                    ];
                                }
                            }
                        }
                        ?>

                        // Initialize the calendar with admin-defined date ranges
                        OktoberfestCalendar.init({
                            container: $('#oktoberfest-calendar'),
                            startDate: '<?php echo esc_js($start_date); ?>',
                            endDate: '<?php echo esc_js($end_date); ?>',
                            selectedDate: '<?php echo esc_js($date); ?>',
                            dateRanges: <?php echo json_encode($calendar_date_ranges); ?>,

                            compact: false,
                            onDateSelect: function(selectedDate) {
                                // Update hidden input
                                $('input[name="selected_date"]').val(selectedDate);

                                // Update display if needed
                                if (selectedDate) {
                                    const dateObj = new Date(selectedDate);
                                    const options = {
                                        weekday: 'long',
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric'
                                    };
                                    const formattedDate = dateObj.toLocaleDateString('en-US', options);
                                    // Update any date display elements if needed
                                }
                            }
                        });
                    }
                });
            </script>

            <form id="everliz-booking-form" method="POST">
                <!-- Hidden fields for date and location -->
                <input type="hidden" name="selected_date" value="<?php echo esc_attr($date); ?>">
                <input type="hidden" name="selected_location" value="<?php echo esc_attr($location); ?>">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('oktoberfest_booking_nonce'); ?>">

                <!-- Attendees and Session Selection -->
                <div class="form-row">
                    <div class="form-group half">
                        <label for="attendees" class="required-field">Number of attendees </label>
                        <select name="attendees" id="attendees" required>
                            <option value="">Select number of attendees</option>
                            <?php foreach ([1, 2, 3, 4, 5, 6, 8, 10, 12, 15, 20] as $option) : ?>
                                <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group half">
                        <label for="session" class="required-field">Choose a session</label>
                        <select name="session" id="session" required>
                            <option value="">Select a session</option>
                            <option value="day">Day session (approx. 8 am – 4 pm)</option>
                            <option value="evening">Evening session (approx. 5 pm – 11 pm)</option>
                        </select>
                    </div>
                </div>

                <!-- Tent Selection -->
                <div class="tent-selection">
                    <p class="section-title">Tent talk</p>
                    <div class="tent-preference">
                        <div class="preference-option<?php echo ($tent_preference === 'any') ? ' selected' : ''; ?>">
                            <div class="radio-col">
                                <input type="radio" id="any-tent" name="tent_preference" value="any"
                                    <?php checked($tent_preference, 'any'); ?>>
                            </div>
                            <div class="content-col">
                                <span class="option-title">Any big tent</span>
                                <p class="preference-description">I have no particular tent preference - any of the 14 big beer
                                    tents will do, as long as there's cold beer, good food, and a lively Oktoberfest atmosphere!
                                </p>
                            </div>
                        </div>

                        <div class="preference-option<?php echo ($tent_preference === 'specific') ? ' selected' : ''; ?>">
                            <div class="radio-col">
                                <input type="radio" id="specific-tent" name="tent_preference" value="specific"
                                    <?php checked($tent_preference, 'specific'); ?>>
                            </div>
                            <div class="content-col">
                                <span class="option-title">Specific tent preference</span>
                                <p class="preference-description">The tent matters to me - I want to choose a specific one.</p>
                            </div>
                        </div>
                    </div>

                    <div class="tent-gallery" id="tent-gallery"
                        style="<?php echo ($tent_preference === 'any') ? 'display: none;' : ''; ?>">
                        <?php
                        foreach ($tents as $tent) : ?>
                            <div class="tent-card <?php echo ($location === $tent['id']) ? 'selected' : ''; ?>"
                                data-tent-id="<?php echo esc_attr($tent['id']); ?>">
                                <div class="tent-image" style="background-image: url('<?php echo esc_url($tent['image']); ?>');">
                                    <div class="tent-name"><?php echo esc_html($tent['name']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="selected_tent" id="selected-tent" value="<?php echo esc_attr($location); ?>">
                </div>

                <!-- Contact Information -->
                <div class="contact-section">
                    <h3>How should we contact you</h3>

                    <div class="form-row">
                        <div class="form-group half">
                            <label for="first_name" class="required-field">First Name </label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>

                        <div class="form-group half">
                            <label for="last_name" class="required-field">Last Name </label>
                            <input type="text" id="last_name" name="last_name" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group half">
                            <label for="email" class="required-field">Email Address </label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="form-group half">
                            <label for="phone" class="required-field">Phone Number </label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="company">Company (optional)</label>
                        <input type="text" id="company" name="company">
                    </div>

                    <div class="form-group">
                        <label for="message">Anything else you'd like to tell us?</label>
                        <textarea id="message" name="message" rows="5"></textarea>
                    </div>
                </div>

                <!-- Form submission button -->
                <div class="form-submit">
                    <button type="submit" class="submit-button">Submit inquiry</button>
                </div>
            </form>
        </div>

        <script>
            jQuery(document).ready(function($) {
                // Initialize calendar if available
                if (typeof OktoberfestCalendar !== 'undefined' && window.OktoberfestDateRanges) {
                    const calendarContainer = $('#oktoberfest-calendar');
                    const selectedDateInput = $('#selected_date');
                    
                    if (calendarContainer.length) {
                        OktoberfestCalendar.init({
                            container: calendarContainer,
                            startDate: window.OktoberfestSettings.startDate,
                            endDate: window.OktoberfestSettings.endDate,
                            selectedDate: window.OktoberfestSettings.selectedDate,
                            dateRanges: window.OktoberfestDateRanges,
                            compact: false,
                            onDateSelect: function(selectedDate) {
                                selectedDateInput.val(selectedDate).trigger('change');
                            }
                        });
                    }
                }

                // Tent preference handling
                $('input[name="tent_preference"]').on('change', function() {
                    const preference = $(this).val();
                    const tentGallery = $('#tent-gallery');
                    const selectedTentInput = $('#selected-tent');
                    
                    $('.preference-option').removeClass('selected');
                    $(this).closest('.preference-option').addClass('selected');
                    
                    if (preference === 'specific') {
                        tentGallery.slideDown(300);
                    } else {
                        tentGallery.slideUp(300);
                        selectedTentInput.val('any');
                        $('.tent-card').removeClass('selected');
                    }
                });

                // Tent card selection
                $('.tent-card').on('click', function() {
                    const tentId = $(this).data('tent-id');
                    const selectedTentInput = $('#selected-tent');
                    
                    $('.tent-card').removeClass('selected');
                    $(this).addClass('selected');
                    selectedTentInput.val(tentId);
                    
                    $('input[name="tent_preference"][value="specific"]').prop('checked', true).trigger('change');
                });

                // Preference option click handling
                $('.preference-option').on('click', function(e) {
                    if (!$(e.target).is('input[type="radio"]')) {
                        $(this).find('input[type="radio"]').prop('checked', true).trigger('change');
                    }
                });

                // Form submission
                $('#everliz-booking-form').on('submit', function(e) {
                    e.preventDefault();
                    
                    if (validateBookingForm()) {
                        submitBookingForm($(this));
                    }
                });
                
                function validateBookingForm() {
                    let isValid = true;
                    const form = $('#everliz-booking-form');
                    
                    // Clear previous errors
                    form.find('.error-message').remove();
                    form.find('.error').removeClass('error');
                    
                    // Required field validation
                    form.find('[required]').each(function() {
                        const field = $(this);
                        if (!field.val() || field.val().trim() === '') {
                            showFieldError(field, 'This field is required');
                            isValid = false;
                        }
                    });
                    
                    // Email validation
                    const emailField = form.find('input[type="email"]');
                    if (emailField.val() && !isValidEmail(emailField.val())) {
                        showFieldError(emailField, 'Please enter a valid email address');
                        isValid = false;
                    }
                    
                    // Tent preference validation
                    const tentPreference = $('input[name="tent_preference"]:checked').val();
                    if (tentPreference === 'specific' && !$('#selected-tent').val()) {
                        showFieldError($('#tent-gallery'), 'Please select a tent');
                        isValid = false;
                    }
                    
                    if (!isValid) {
                        $('html, body').animate({
                            scrollTop: $('.error').first().offset().top - 100
                        }, 500);
                    }
                    
                    return isValid;
                }
                
                function showFieldError(field, message) {
                    field.addClass('error');
                    const errorEl = $('<div class="error-message" style="color: #e74c3c; font-size: 14px; margin-top: 5px;">' + message + '</div>');
                    field.after(errorEl);
                }
                
                function isValidEmail(email) {
                    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return regex.test(email);
                }
                
                function submitBookingForm(form) {
                    const submitButton = form.find('button[type="submit"]');
                    const originalText = submitButton.text();
                    
                    submitButton.prop('disabled', true).html('<span>Processing...</span>');
                    
                    const formData = new FormData(form[0]);
                    formData.append('action', 'oktoberfest_submit_booking');
                    
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                // Check if we have a redirect URL
                                if (response.data && response.data.redirect_url) {
                                    // Redirect to thank you page
                                    window.location.href = response.data.redirect_url;
                                } else {
                                    // Show success message if no redirect URL
                                    const message = response.data && response.data.message ? response.data.message : 'Booking submitted successfully!';
                                    form.html('<div style="text-align: center; padding: 2rem; background: #4CAF50; color: white; border-radius: 8px;"><h3>Thank you!</h3><p>' + message + '</p></div>');
                                }
                            } else {
                                alert('Error: ' + (response.data || 'Unknown error occurred'));
                            }
                        },
                        error: function() {
                            alert('Unable to process your request. Please try again later.');
                        },
                        complete: function() {
                            submitButton.prop('disabled', false).html(originalText);
                        }
                    });
                }
            });
        </script>
<?php
    }

    public static function sanitize_date_ranges($input)
    {
        $sanitized = [];
        if (is_array($input)) {
            foreach ($input as $row) {
                if (
                    !empty($row['year']) &&
                    !empty($row['start_date']) &&
                    !empty($row['end_date'])
                ) {
                    $sanitized[] = [
                        'year' => intval($row['year']),
                        'start_date' => sanitize_text_field($row['start_date']),
                        'end_date' => sanitize_text_field($row['end_date'])
                    ];
                }
            }
        }
        // Re-index to ensure keys are 0,1,2,...
        $sanitized = array_values($sanitized);

        // Always return at least one default if empty
        if (empty($sanitized)) {
            $sanitized[] = [
                'year' => 2025,
                'start_date' => '2025-09-20',
                'end_date' => '2025-10-05'
            ];
        }
        return $sanitized;
    }
}
