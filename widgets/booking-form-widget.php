<?php

namespace Everliz_Oktoberfest;

if (!defined('ABSPATH')) exit;

use Oktoberfest_VIP\API_Handler;

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

        $tents = API_Handler::get_local_tents();
?>
        <div class="booking-form-container">
            <div id="booking-summary" style="margin-bottom:2em;"></div>
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
                            minYear: 2025,
                            maxYear: 2028,
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

            <form id="vip-booking-form" method="POST">
                <!-- Hidden fields for date and location -->
                <input type="hidden" name="selected_date" value="<?php echo esc_attr($date); ?>">
                <input type="hidden" name="selected_location" value="<?php echo esc_attr($location); ?>">

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
                                    tents will do, as long as there's cold beer, good food, and a lively Oktoberfest atmosphere!</p>
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

                    <div class="form-group checkbox">
                        <input type="checkbox" id="newsletter" name="newsletter" checked>
                        <label for="newsletter">Send me exclusive Oktoberfest tips, early bird deals, and special
                            offers.</label>
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
                // Initialize calendar if OktoberfestCalendar is available
                if (typeof OktoberfestCalendar !== 'undefined') {
                    // Initialize the calendar
                    OktoberfestCalendar.init({
                        container: $('#search-calendar'),
                        startDate: '<?php echo esc_js($start_date); ?>',
                        endDate: '<?php echo esc_js($end_date); ?>',
                        inputField: $('#booking_date'),
                        compact: true,
                        popupElement: $('#date-popup')
                    });

                    // Toggle date popup
                    $('#selected-date-display').on('click', function(e) {
                        e.stopPropagation();
                        $('#date-popup').toggleClass('active');
                        return false;
                    });

                    // Stop propagation on popup clicks to prevent closing
                    $('#date-popup').on('click', function(e) {
                        e.stopPropagation();
                    });

                    // Close popup when clicking outside
                    $(document).on('click', function() {
                        $('#date-popup').removeClass('active');
                    });

                    // Prevent year navigation from closing popup
                    $(document).on('click', '.year-nav .prev-year, .year-nav .next-year', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    });

                    // Update selected date display when date changes
                    $('#booking_date').on('change', function() {
                        const dateValue = $(this).val();
                        if (dateValue) {
                            const dateObj = new Date(dateValue);
                            const options = {
                                weekday: 'long',
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            };
                            const formattedDate = dateObj.toLocaleDateString('en-US', options);
                            $('#selected-date-display').text(formattedDate);
                            $('#date-popup').removeClass('active');
                        }
                    });
                }

                // Form submission
                $('#reservation-form').on('submit', function(e) {
                    e.preventDefault();

                    // Validate form
                    if (!$('#booking_date').val()) {
                        alert('Please select a date');
                        return;
                    }

                    if (!$('#tent').val()) {
                        alert('Please select a tent');
                        return;
                    }

                    // Get form values
                    const date = $('#booking_date').val();
                    const location = $('#tent').val();

                    // Base64 encode the values as specified in the requirements
                    const encodedDate = btoa(date);
                    const encodedLocation = btoa(location);

                    // Construct the URL with parameters
                    const bookingUrl = '<?php echo esc_url($booking_page_url); ?>' +
                        '?date=' + encodedDate +
                        '&location=' + encodedLocation;

                    // Redirect to booking page
                    window.location.href = bookingUrl;
                });
            });
        </script>
<?php
    }
}
