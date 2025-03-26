<?php
namespace Everliz_Oktoberfest;

if (!defined('ABSPATH')) exit;

class Reservation_Form_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'everliz_oktoberfest_reservation';
    }

    public function get_title() {
        return __('Oktoberfest VIP Reservation', 'everliz-oktoberfest');
    }

    public function get_icon() {
        return 'eicon-form-horizontal';
    }

    public function get_categories() {
        return ['basic'];
    }

    protected function register_controls() {
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
                'default' => __('Request', 'everliz-oktoberfest'),
            ]
        );
        
        // Add date range controls
        $this->add_control(
            'date_range_heading',
            [
                'label' => __('Oktoberfest Date Range', 'everliz-oktoberfest'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        
        $this->add_control(
            'start_date',
            [
                'label' => __('Start Date', 'everliz-oktoberfest'),
                'type' => \Elementor\Controls_Manager::DATE_TIME,
                'default' => '2025-09-20',
                'picker_options' => [
                    'enableTime' => false,
                    'dateFormat' => 'Y-m-d',
                ],
            ]
        );
        
        $this->add_control(
            'end_date',
            [
                'label' => __('End Date', 'everliz-oktoberfest'),
                'type' => \Elementor\Controls_Manager::DATE_TIME,
                'default' => '2025-10-05',
                'picker_options' => [
                    'enableTime' => false,
                    'dateFormat' => 'Y-m-d',
                ],
            ]
        );
        
        $this->add_control(
            'booking_page',
            [
                'label' => __('Booking Page URL', 'everliz-oktoberfest'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => home_url('/booking-page/'),
                'description' => __('URL of the page with the booking form', 'everliz-oktoberfest'),
            ]
        );
        
        $this->end_controls_section();
    }
    protected function render() {
        $settings = $this->get_settings_for_display();
        $booking_page_url = !empty($settings['booking_page']) ? $settings['booking_page'] : home_url('/booking-page/');
        
        // Get date range from widget settings
        $start_date = isset($settings['start_date']) ? $settings['start_date'] : '2025-09-20';
        $end_date = isset($settings['end_date']) ? $settings['end_date'] : '2025-10-05';
        ?>

<div class="everliz-reservation-form">
    <form id="reservation-form" method="GET" action="<?php echo esc_url($booking_page_url); ?>">
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

        <button type="submit" class="reservation-submit">
            <?php echo esc_html($settings['button_text']); ?>
        </button>
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
            compact: false,
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