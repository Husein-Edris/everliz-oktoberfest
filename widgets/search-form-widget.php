<?php

namespace Everliz_Oktoberfest;

if (!defined('ABSPATH')) exit;

class Search_Form_Widget extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'everliz_oktoberfest_search';
    }

    public function get_title()
    {
        return __('Oktoberfest VIP Search', 'everliz-oktoberfest');
    }

    public function get_icon()
    {
        return 'eicon-search';
    }

    public function get_categories()
    {
        return ['basic'];
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Form Settings', 'everliz-oktoberfest'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'date_placeholder',
            [
                'label' => __('Date Placeholder', 'everliz-oktoberfest'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Select Date', 'everliz-oktoberfest'),
            ]
        );

        $this->add_control(
            'location_placeholder',
            [
                'label' => __('Location Placeholder', 'everliz-oktoberfest'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('TENTS', 'everliz-oktoberfest'),
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => __('Button Text', 'everliz-oktoberfest'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Search', 'everliz-oktoberfest'),
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        // Get booking page URL from plugin settings
        $general_settings = get_option('oktoberfest_general_settings', []);
        $booking_page_id = isset($general_settings['booking_page']) ? $general_settings['booking_page'] : 0;
        $booking_page_url = $booking_page_id ? get_permalink($booking_page_id) : home_url('/booking/');

        // Get date ranges from WordPress options with proper type checking
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

        <div class="everliz-search-form">
            <form id="search-form" method="GET" action="<?php echo esc_url($booking_page_url); ?>">
                <div class="form-group date-picker-container">
                    <label><?php echo esc_html($settings['date_placeholder']); ?></label>
                    <div class="date-select-wrapper">
                        <div class="selected-date-display" id="selected-date-display">
                            <?php echo esc_html($settings['date_placeholder']); ?></div>
                        <div class="date-popup" id="date-popup">
                            <div class="calendar-wrapper" id="search-calendar">
                                <!-- Calendar -->
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="date" id="booking_date" value="">
                </div>

                <div class="form-group">
                    <label><?php echo esc_html($settings['location_placeholder']); ?></label>
                    <select name="location" id="tent" required>
                        <option value=""><?php echo esc_html($settings['location_placeholder']); ?></option>
                        <option value="any">Any Tent</option>
                        <option value="hofbrau">Hofbräu-Festzelt</option>
                        <option value="augustiner">Augustiner-Festhalle</option>
                        <option value="paulaner">Paulaner-Festzelt</option>
                    </select>
                </div>

                <button type="submit" class="search-submit">
                    <?php echo esc_html($settings['button_text']); ?>
                </button>
            </form>
        </div>
        <script>
            jQuery(document).ready(function($) {
                // Initialize calendar if OktoberfestCalendar is available
                if (typeof OktoberfestCalendar !== 'undefined') {
                    // Initialize the calendar with admin-defined date ranges
                    OktoberfestCalendar.init({
                        container: $('#search-calendar'),
                        startDate: '<?php echo esc_js($start_date); ?>',
                        endDate: '<?php echo esc_js($end_date); ?>',
                        inputField: $('#booking_date'),
                        compact: false,
                        popupElement: $('#date-popup'),
                        dateRanges: <?php echo json_encode($calendar_date_ranges); ?>,
                        minYear: 2025,
                        maxYear: 2028
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
                $('#search-form').on('submit', function(e) {
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
