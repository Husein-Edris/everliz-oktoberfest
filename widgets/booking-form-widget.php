<?php
namespace Everliz_Oktoberfest;

if (!defined('ABSPATH')) exit;

class Booking_Form_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'everliz_oktoberfest_booking_form';
    }

    public function get_title() {
        return __('Oktoberfest VIP Booking Form', 'everliz-oktoberfest');
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
            'thank_you_page',
            [
                'label' => __('Thank You Page URL', 'everliz-oktoberfest'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => home_url('/thank-you/'),
                'description' => __('URL to redirect after successful form submission', 'everliz-oktoberfest'),
            ]
        );

        $this->add_control(
            'submit_button_text',
            [
                'label' => __('Submit Button Text', 'everliz-oktoberfest'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Submit Inquiry', 'everliz-oktoberfest'),
            ]
        );

        $this->end_controls_section();

        // Style controls
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Form Style', 'everliz-oktoberfest'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'form_background',
            [
                'label' => __('Form Background', 'everliz-oktoberfest'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#111827',
                'selectors' => [
                    '{{WRAPPER}} .booking-form-container' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'form_text_color',
            [
                'label' => __('Text Color', 'everliz-oktoberfest'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .booking-form-container' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'button_background',
            [
                'label' => __('Button Background', 'everliz-oktoberfest'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#F59E0B',
                'selectors' => [
                    '{{WRAPPER}} .submit-button' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Get URL parameters
        $encoded_date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : '';
        $encoded_location = isset($_GET['location']) ? sanitize_text_field($_GET['location']) : '';
        
        // Decode parameters
        $date = '';
        $location = '';
        
        if (!empty($encoded_date)) {
            $date = base64_decode($encoded_date);
        }
        
        if (!empty($encoded_location)) {
            $location = base64_decode($encoded_location);
        }
        
        // Format date for display
        $display_date = !empty($date) ? date('F j, Y', strtotime($date)) : '';
        
        // For this example, we'll use static options, but these would come from API in production
        $attendee_options = [1, 2, 3, 4, 5, 6, 8, 10, 12, 15, 20];
        $sessions = [
            'day' => __('Day session (approx. 8 am – 4 pm)', 'everliz-oktoberfest'),
            'evening' => __('Evening session (approx. 5 pm – 11 pm)', 'everliz-oktoberfest')
        ];
        
        // Tent options with images (these would come from API)
        $tents = [
            [
                'id' => 'armbrustschutzenzelt',
                'name' => 'Armbrustschützenzelt',
                'image' => plugins_url('assets/images/tents/armbrustschutzenzelt.jpg', dirname(__FILE__))
            ],
            [
                'id' => 'augustiner',
                'name' => 'Augustiner-Festhalle',
                'image' => plugins_url('assets/images/tents/augustiner.jpg', dirname(__FILE__))
            ],
            [
                'id' => 'fischer-vroni',
                'name' => 'Fischer-Vroni',
                'image' => plugins_url('assets/images/tents/fischer-vroni.jpg', dirname(__FILE__))
            ],
            [
                'id' => 'hacker-festzelt',
                'name' => 'Hacker-Festzelt',
                'image' => plugins_url('assets/images/tents/hacker-festzelt.jpg', dirname(__FILE__))
            ],
            [
                'id' => 'hofbrau',
                'name' => 'Hofbräu-Festzelt',
                'image' => plugins_url('assets/images/tents/hofbrau.jpg', dirname(__FILE__))
            ],
            [
                'id' => 'kafer-wiesn-schanke',
                'name' => 'Käfer Wiesn-Schänke',
                'image' => plugins_url('assets/images/tents/kafer-wiesn-schanke.jpg', dirname(__FILE__))
            ]
        ];
        
        // Add nonce for form submission
        $nonce = wp_create_nonce('everliz_booking_form_nonce');
        ?>
<div class="booking-form-container">
    <!-- Calendar section -->
    <div class="calendar-section">
        <div class="year-nav">
            <span class="prev-year">&#10094;</span>
            <h2>2025</h2>
            <span class="next-year">&#10095;</span>
        </div>

        <div class="date-selection">
            <p class="required-field">Choose a reservation date *</p>
            <div class="calendar-wrapper">
                <!-- Calendar would be rendered here with JavaScript -->
                <div class="month-header">
                    <?php echo !empty($display_date) ? date('F Y', strtotime($date)) : 'September 2025'; ?></div>

                <!-- This is a simplified representation - would be replaced with a real calendar -->
                <div class="calendar-grid">
                    <div class="calendar-days">
                        <div>Sun</div>
                        <div>Mon</div>
                        <div>Tue</div>
                        <div>Wed</div>
                        <div>Thu</div>
                        <div>Fri</div>
                        <div>Sat</div>
                    </div>
                    <div class="calendar-dates">
                        <!-- Calendar dates would be dynamically generated -->
                        <!-- For now, just highlighting the selected date or today if none selected -->
                        <div
                            class="<?php echo !empty($date) && date('d', strtotime($date)) === '24' ? 'selected' : ''; ?>">
                            24</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="vip-booking-form" method="POST">
        <!-- Hidden fields for date and location -->
        <input type="hidden" name="selected_date" value="<?php echo esc_attr($date); ?>">
        <input type="hidden" name="selected_location" value="<?php echo esc_attr($location); ?>">

        <!-- Attendees and Session Selection -->
        <div class="form-row">
            <div class="form-group half">
                <label for="attendees" class="required-field">Number of attendees *</label>
                <select name="attendees" id="attendees" required>
                    <?php foreach ($attendee_options as $option) : ?>
                    <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group half">
                <label for="session" class="required-field">Choose a session *</label>
                <select name="session" id="session" required>
                    <?php foreach ($sessions as $key => $label) : ?>
                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Tent Selection -->
        <div class="tent-selection">
            <p class="section-title">Tent talk</p>

            <div class="tent-preference">
                <div class="preference-option">
                    <input type="radio" id="any-tent" name="tent_preference" value="any"
                        <?php checked($location, 'any'); ?>>
                    <label for="any-tent">Any big tent</label>
                    <p class="preference-description">I have no particular tent preference - any of the 14 big beer
                        tents will do, as long as there's cold beer, good food, and a lively Oktoberfest atmosphere!</p>
                </div>

                <div class="preference-option">
                    <input type="radio" id="specific-tent" name="tent_preference" value="specific"
                        <?php echo ($location !== 'any') ? 'checked' : ''; ?>>
                    <label for="specific-tent">Specific tent preference</label>
                    <p class="preference-description">The tent matters to me - I want to choose a specific one.</p>
                </div>
            </div>

            <div class="tent-gallery" id="tent-gallery">
                <?php foreach ($tents as $tent) : ?>
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
                    <label for="first_name" class="required-field">First Name *</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>

                <div class="form-group half">
                    <label for="last_name" class="required-field">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <label for="email" class="required-field">Email Address *</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group half">
                    <label for="phone" class="required-field">Phone Number *</label>
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

        <!-- Security fields -->
        <input type="hidden" name="action" value="everliz_submit_booking">
        <input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>">

        <div class="form-submit">
            <button type="submit"
                class="submit-button"><?php echo esc_html($settings['submit_button_text']); ?></button>
        </div>
    </form>

    <div class="support-section">
        <p>Need support?</p>
        <button class="call-button">Give Us a Call</button>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Toggle tent gallery based on preference selection
    $('input[name="tent_preference"]').on('change', function() {
        if ($(this).val() === 'specific') {
            $('#tent-gallery').slideDown();
        } else {
            $('#tent-gallery').slideUp();
            $('#selected-tent').val('any');
        }
    });

    // Initialize tent gallery visibility
    if ($('input[name="tent_preference"]:checked').val() === 'any') {
        $('#tent-gallery').hide();
    }

    // Handle tent selection
    $('.tent-card').on('click', function() {
        $('.tent-card').removeClass('selected');
        $(this).addClass('selected');
        $('#selected-tent').val($(this).data('tent-id'));

        // Ensure "specific tent" option is selected
        $('#specific-tent').prop('checked', true);
    });

    // Form submission
    $('#vip-booking-form').on('submit', function(e) {
        e.preventDefault();

        // Here you would normally handle the AJAX form submission
        // For this example, we'll just redirect to the thank you page

        alert('Form submitted! In a real implementation, this would send the data to your server.');
        window.location.href = '<?php echo esc_url($settings['thank_you_page']); ?>';
    });
});
</script>
<?php
    }
}