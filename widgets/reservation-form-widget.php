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

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        ?>
<div class="everliz-reservation-form">
    <form id="reservation-form" method="GET" action="<?php echo esc_url(home_url('/')); ?>">
        <div class="form-group">
            <label>Select Date</label>
            <input type="date" name="booking_date" id="booking_date" required>
        </div>
        <div class="form-group">
            <label>Select Location</label>
            <select name="tent" id="tent" required>
                <option value="">TENTS</option>
                <option value="hofbrau">Hofbräu-Festzelt</option>
                <option value="augustiner">Augustiner-Festhalle</option>
                <option value="paulaner">Paulaner-Festzelt</option>
            </select>
        </div>
        <button type="submit" class="reservation-submit">
            Request
        </button>
    </form>
</div>
<?php
    }
}