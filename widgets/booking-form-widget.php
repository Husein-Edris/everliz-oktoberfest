<?php

namespace Everliz_Oktoberfest;

if (!defined('ABSPATH')) exit;

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

?>
<div class="booking-form-container">
    <div class="calendar-section">
        <div class="calendar-wrapper" id="oktoberfest-calendar">
            <!-- Calendar -->
        </div>
        <input type="hidden" name="selected_date" value="<?php echo esc_attr($date); ?>">
    </div>
    <script>
    jQuery(document).ready(function($) {
        // Get Oktoberfest date range from WordPress
        <?php
                    $options = get_option('oktoberfest_vip_settings', []);
                    $start_date = isset($options['oktoberfest_start_date']) ? $options['oktoberfest_start_date'] : '2025-09-20';
                    $end_date = isset($options['oktoberfest_end_date']) ? $options['oktoberfest_end_date'] : '2025-10-05';
                    ?>

        OktoberfestCalendar.init({
            container: $('#oktoberfest-calendar'),
            startDate: '<?php echo esc_js($start_date); ?>',
            endDate: '<?php echo esc_js($end_date); ?>',
            selectedDate: '<?php echo esc_js($date); ?>'
        });
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
                <div class="preference-option">
                    <input type="radio" id="any-tent" name="tent_preference" value="any"
                        <?php checked($tent_preference, 'any'); ?>>
                    <label for="any-tent">Any big tent</label>
                    <p class="preference-description">I have no particular tent preference - any of the 14 big beer
                        tents will do, as long as there's cold beer, good food, and a lively Oktoberfest atmosphere!</p>
                </div>

                <div class="preference-option">
                    <input type="radio" id="specific-tent" name="tent_preference" value="specific"
                        <?php checked($tent_preference, 'specific'); ?>>
                    <label for="specific-tent">Specific tent preference</label>
                    <p class="preference-description">The tent matters to me - I want to choose a specific one.</p>
                </div>
            </div>

            <div class="tent-gallery" id="tent-gallery"
                style="<?php echo ($tent_preference === 'any') ? 'display: none;' : ''; ?>">
                <?php
                        // Define tent options
                        $tents = [
                            [
                                'id' => 'armbrustschutzenzelt',
                                'name' => 'Armbrustschützenzelt',
                                'image' => plugin_dir_url(dirname(__FILE__)) . 'assets/images/tents/armbrustschutzenzelt.jpg'
                            ],
                            [
                                'id' => 'augustiner',
                                'name' => 'Augustiner-Festhalle',
                                'image' => plugin_dir_url(dirname(__FILE__)) . 'assets/images/tents/augustiner.jpg'
                            ],
                            [
                                'id' => 'fischer-vroni',
                                'name' => 'Fischer-Vroni',
                                'image' => plugin_dir_url(dirname(__FILE__)) . 'assets/images/tents/fischer-vroni.jpg'
                            ],
                            [
                                'id' => 'hacker-festzelt',
                                'name' => 'Hacker-Festzelt',
                                'image' => plugin_dir_url(dirname(__FILE__)) . 'assets/images/tents/hacker-festzelt.jpg'
                            ],
                            [
                                'id' => 'hofbrau',
                                'name' => 'Hofbräu-Festzelt',
                                'image' => plugin_dir_url(dirname(__FILE__)) . 'assets/images/tents/hofbrau.jpg'
                            ],
                            [
                                'id' => 'kafer-wiesn-schanke',
                                'name' => 'Käfer Wiesn-Schänke',
                                'image' => plugin_dir_url(dirname(__FILE__)) . 'assets/images/tents/kafer-wiesn-schanke.jpg'
                            ]
                        ];

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