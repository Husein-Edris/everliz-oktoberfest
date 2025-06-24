<?php

/**
 * Plugin Name: Oktoberfest VIP Booking
 * Description: Custom Elementor widgets for Oktoberfest VIP booking
 * Version: 1.5
 * Author: Edris Husein
 * Author URI: edrishusein.com
 */

// Todo
// #Danke seite slecte in the backend 


if (!defined('ABSPATH')) exit;

class Oktoberfest_VIP_Booking
{
    private static $_instance = null;
    private $plugin_path;
    private $plugin_url;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        $this->plugin_path = plugin_dir_path(__FILE__);
        $this->plugin_url = plugin_dir_url(__FILE__);

        add_action('plugins_loaded', [$this, 'init']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
    }

    public function admin_enqueue_scripts($hook)
    {
        // Enqueue on all admin pages that start with oktoberfest
        if (strpos($hook, 'oktoberfest') === false) {
            return;
        }

        wp_enqueue_style(
            'oktoberfest-admin-styles',
            $this->plugin_url . 'assets/css/admin.css',
            [],
            filemtime($this->plugin_path . 'assets/css/admin.css')
        );

        wp_enqueue_script(
            'oktoberfest-admin-script',
            $this->plugin_url . 'assets/js/admin.js',
            ['jquery'],
            filemtime($this->plugin_path . 'assets/js/admin.js'),
            true
        );
    }

    public function init()
    {
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-warning is-dismissible">';
                echo '<p>Elementor is required for the Oktoberfest VIP Booking System.</p>';
                echo '</div>';
            });
            return;
        }

        add_action('elementor/widgets/register', [$this, 'register_widgets']);
    }

    public function enqueue_assets()
    {
        wp_enqueue_style(
            'oktoberfest-styles',
            $this->plugin_url . 'assets/css/main.css',
            [],
            filemtime($this->plugin_path . 'assets/css/main.css')
        );

        wp_enqueue_script(
            'oktoberfest-calendar',
            $this->plugin_url . 'assets/js/calendar.js',
            ['jquery'],
            filemtime($this->plugin_path . 'assets/js/calendar.js'),
            true
        );

        wp_enqueue_script(
            'oktoberfest-main',
            $this->plugin_url . 'assets/js/main.js',
            ['jquery', 'oktoberfest-calendar'],
            filemtime($this->plugin_path . 'assets/js/main.js'),
            true
        );

        // Localize script for AJAX
        wp_localize_script(
            'oktoberfest-main',
            'OktoberfestAjax',
            [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('oktoberfest_booking_nonce'),
                'messages' => [
                    'processing' => __('Processing...', 'everliz-oktoberfest'),
                    'success' => __('Booking submitted successfully!', 'everliz-oktoberfest'),
                    'error' => __('An error occurred. Please try again.', 'everliz-oktoberfest'),
                    'validation_required' => __('This field is required', 'everliz-oktoberfest'),
                    'validation_email' => __('Please enter a valid email address', 'everliz-oktoberfest'),
                    'validation_tent' => __('Please select a tent', 'everliz-oktoberfest')
                ]
            ]
        );
    }

    public function register_widgets($widgets_manager)
    {
        require_once($this->plugin_path . '/includes/class-api-handler.php');
        require_once($this->plugin_path . '/widgets/search-form-widget.php');
        require_once($this->plugin_path . '/widgets/booking-form-widget.php');
        $widgets_manager->register(new \Everliz_Oktoberfest\Search_Form_Widget());
        $widgets_manager->register(new \Everliz_Oktoberfest\Booking_Form_Widget());
    }

    public function add_admin_menu()
    {
        add_menu_page(
            'Oktoberfest Settings',
            'Oktoberfest',
            'manage_options',
            'oktoberfest-settings',
            [$this, 'render_settings_page'],
            'dashicons-calendar-alt'
        );
    }

    public function register_settings()
    {
        // General Settings with proper sanitization
        register_setting('oktoberfest_general_settings', 'oktoberfest_general_settings', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_general_settings']
        ]);

        // Date Ranges Settings with robust sanitization
        register_setting('oktoberfest_date_ranges', 'oktoberfest_date_ranges', [
            'type' => 'array',
            'sanitize_callback' => [self::class, 'sanitize_date_ranges']
        ]);

        // API Settings
        register_setting('oktoberfest_api_settings', 'oktoberfest_api_settings', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_api_settings']
        ]);

        // General Settings Section
        add_settings_section(
            'oktoberfest_general_section',
            'General Settings',
            [$this, 'render_general_section_info'],
            'oktoberfest-settings'
        );

        // Date Ranges Section
        add_settings_section(
            'oktoberfest_dates_section',
            'Event Date Ranges',
            [$this, 'render_dates_section_info'],
            'oktoberfest-settings'
        );

        // API Settings Section
        add_settings_section(
            'oktoberfest_api_section',
            'API Configuration',
            [$this, 'render_api_section_info'],
            'oktoberfest-settings'
        );

        // General Fields
        add_settings_field(
            'booking_page',
            'Booking Page',
            [$this, 'render_booking_page_field'],
            'oktoberfest-settings',
            'oktoberfest_general_section'
        );

        add_settings_field(
            'thank_you_page',
            'Thank You Page (Danke Seite)',
            [$this, 'render_thank_you_page_field'],
            'oktoberfest-settings',
            'oktoberfest_general_section'
        );

        // API Fields
        add_settings_field(
            'api_key',
            'API Key',
            [$this, 'render_api_key_field'],
            'oktoberfest-settings',
            'oktoberfest_api_section'
        );

        add_settings_field(
            'api_secret',
            'API Secret',
            [$this, 'render_api_secret_field'],
            'oktoberfest-settings',
            'oktoberfest_api_section'
        );

        add_settings_field(
            'api_endpoint',
            'API Endpoint',
            [$this, 'render_api_endpoint_field'],
            'oktoberfest-settings',
            'oktoberfest_api_section'
        );
    }

    public function sanitize_general_settings($input)
    {
        $sanitized = [];

        if (isset($input['booking_page'])) {
            $sanitized['booking_page'] = intval($input['booking_page']);
        }

        if (isset($input['thank_you_page'])) {
            $sanitized['thank_you_page'] = intval($input['thank_you_page']);
        }

        return $sanitized;
    }

    public function sanitize_api_settings($input)
    {
        $sanitized = [];

        if (isset($input['api_key'])) {
            $sanitized['api_key'] = sanitize_text_field($input['api_key']);
        }

        if (isset($input['api_secret'])) {
            $sanitized['api_secret'] = sanitize_text_field($input['api_secret']);
        }

        if (isset($input['api_endpoint'])) {
            $sanitized['api_endpoint'] = esc_url_raw($input['api_endpoint']);
        }

        return $sanitized;
    }

    // Robust sanitization for date ranges
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

    public function render_settings_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
?>
<div class="wrap oktoberfest-settings">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <h2 class="nav-tab-wrapper">
        <a href="?page=oktoberfest-settings&tab=general"
            class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>">
            General Settings
        </a>
        <a href="?page=oktoberfest-settings&tab=date_ranges"
            class="nav-tab <?php echo $active_tab == 'date_ranges' ? 'nav-tab-active' : ''; ?>">
            Date Ranges
        </a>
        <a href="?page=oktoberfest-settings&tab=api"
            class="nav-tab <?php echo $active_tab == 'api' ? 'nav-tab-active' : ''; ?>">
            API Settings
        </a>
    </h2>

    <?php
            if ($active_tab == 'general') {
                $this->render_general_tab();
            } elseif ($active_tab == 'date_ranges') {
                $this->render_date_ranges_tab();
            } else {
                $this->render_api_settings_tab();
            }
            ?>
</div>
<?php
    }

    private function render_general_tab()
    {
        $general_settings = get_option('oktoberfest_general_settings', []);
    ?>
<div class="general-settings-container">
    <form method="post" action="options.php" class="oktoberfest-form">
        <?php settings_fields('oktoberfest_general_settings'); ?>
        <table class="form-table">
        <tr>
            <th scope="row">
                <label for="booking_page">Booking Page</label>
            </th>
            <td>
                <?php
                        wp_dropdown_pages([
                            'name' => 'oktoberfest_general_settings[booking_page]',
                            'id' => 'booking_page',
                            'show_option_none' => 'Select a page',
                            'option_none_value' => '0',
                            'selected' => $general_settings['booking_page'] ?? 0,
                            'class' => 'regular-text'
                        ]);
                        ?>
                <p class="description">Select the page where your booking form is displayed.</p>
                <div class="page-actions">
                    <a href="<?php echo admin_url('post-new.php?post_type=page'); ?>" class="button">Create New Page</a>
                    <?php if (!empty($general_settings['booking_page'])) : ?>
                    <a href="<?php echo get_edit_post_link($general_settings['booking_page']); ?>" class="button">Edit
                        Page</a>
                    <a href="<?php echo get_permalink($general_settings['booking_page']); ?>" class="button"
                        target="_blank">View Page</a>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="thank_you_page">Thank You Page (Danke Seite)</label>
            </th>
            <td>
                <?php
                        wp_dropdown_pages([
                            'name' => 'oktoberfest_general_settings[thank_you_page]',
                            'id' => 'thank_you_page',
                            'show_option_none' => 'Select a page',
                            'option_none_value' => '0',
                            'selected' => $general_settings['thank_you_page'] ?? 0,
                            'class' => 'regular-text'
                        ]);
                        ?>
                <p class="description">Select the page to redirect to after successful form submission.</p>
                <div class="page-actions">
                    <a href="<?php echo admin_url('post-new.php?post_type=page'); ?>" class="button">Create New Page</a>
                    <?php if (!empty($general_settings['thank_you_page'])) : ?>
                    <a href="<?php echo get_edit_post_link($general_settings['thank_you_page']); ?>" class="button">Edit
                        Page</a>
                    <a href="<?php echo get_permalink($general_settings['thank_you_page']); ?>" class="button"
                        target="_blank">View Page</a>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        </table>
        <?php submit_button('Save General Settings'); ?>
    </form>
</div>
<?php
    }

    private function render_date_ranges_tab()
    {
        // Get date ranges with proper type checking
        $date_ranges = get_option('oktoberfest_date_ranges');

        // Ensure $date_ranges is an array
        if (!is_array($date_ranges)) {
            $date_ranges = [[
                'year' => '2025',
                'start_date' => '2025-09-20',
                'end_date' => '2025-10-05'
            ]];
        }
    ?>
<div class="date-ranges-container">
    <form method="post" action="options.php" class="oktoberfest-form">
        <?php settings_fields('oktoberfest_date_ranges'); ?>
        <div class="date-ranges-header">
            <div class="header-content">
                <h3>Event Date Ranges</h3>
                <p class="description">Set the date ranges for each year's Oktoberfest event.</p>
            </div>
            <div class="header-actions">
                <?php submit_button('Save Date Ranges', 'primary', 'submit', false); ?>
            </div>
        </div>

    <div class="date-ranges-table-container">
        <table id="oktoberfest-dates">
            <thead>
                <tr>
                    <th>Year</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($date_ranges as $index => $range) : ?>
                <tr class="date-range-row">
                    <td>
                        <input type="number" name="oktoberfest_date_ranges[<?php echo $index; ?>][year]"
                            value="<?php echo esc_attr($range['year']); ?>" min="2025" max="2028" required>
                    </td>
                    <td>
                        <input type="date" name="oktoberfest_date_ranges[<?php echo $index; ?>][start_date]"
                            value="<?php echo esc_attr($range['start_date']); ?>" required>
                    </td>
                    <td>
                        <input type="date" name="oktoberfest_date_ranges[<?php echo $index; ?>][end_date]"
                            value="<?php echo esc_attr($range['end_date']); ?>" required>
                    </td>
                    <td>
                        <button type="button" class="button remove-date-range">
                            <span class="dashicons dashicons-trash"></span>
                            Remove
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

        <div class="date-ranges-actions">
            <button type="button" class="button button-primary" id="add-date-range">
                <span class="dashicons dashicons-plus-alt2"></span>
                Add Date Range
            </button>
        </div>
    </form>
</div>
<?php
    }

    private function render_api_settings_tab()
    {
        $api_settings = get_option('oktoberfest_api_settings', []);
    ?>
<div class="api-settings-container">
    <form method="post" action="options.php" class="oktoberfest-form">
        <?php settings_fields('oktoberfest_api_settings'); ?>
        <table class="form-table">
        <tr>
            <th scope="row">
                <label for="api_key">API Key</label>
            </th>
            <td>
                <input type="text" id="api_key" name="oktoberfest_api_settings[api_key]"
                    value="<?php echo esc_attr($api_settings['api_key'] ?? ''); ?>" class="regular-text">
                <p class="description">Enter your API key for authentication.</p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="api_secret">API Secret</label>
            </th>
            <td>
                <input type="password" id="api_secret" name="oktoberfest_api_settings[api_secret]"
                    value="<?php echo esc_attr($api_settings['api_secret'] ?? ''); ?>" class="regular-text">
                <p class="description">Enter your API secret key.</p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="api_endpoint">API Endpoint</label>
            </th>
            <td>
                <input type="url" id="api_endpoint" name="oktoberfest_api_settings[api_endpoint]"
                    value="<?php echo esc_url($api_settings['api_endpoint'] ?? ''); ?>" class="regular-text">
                <p class="description">Enter the API endpoint URL.</p>
            </td>
        </tr>
        </table>
        
        <div style="margin: 20px 0;">
            <button type="button" class="button button-secondary" id="test-api-connection">Test API Connection</button>
            <span id="api-test-result" style="margin-left:1em;"></span>
        </div>
        
        <?php submit_button('Save API Settings'); ?>
    </form>
</div>
<script>
jQuery(document).ready(function($) {
    $('#test-api-connection').on('click', function() {
        var $btn = $(this);
        var $result = $('#api-test-result');
        $result.text('Testing...');
        $btn.prop('disabled', true);
        $.post(ajaxurl, {
            action: 'oktoberfest_test_api_connection'
        }, function(response) {
            $btn.prop('disabled', false);
            if (response.success) {
                $result.html('<span style="color:green;">' + response.data + '</span>');
            } else {
                $result.html('<span style="color:red;">' + response.data + '</span>');
            }
        });
    });
});
</script>
<?php
    }
}

// Initialize the plugin
Oktoberfest_VIP_Booking::instance();

// Add AJAX handler for API test
add_action('wp_ajax_oktoberfest_test_api_connection', function () {
    // Use the API_Handler to test connection
    require_once plugin_dir_path(__FILE__) . 'includes/class-api-handler.php';
    $handler = \Everliz_Oktoberfest\API_Handler::instance();
    if (!$handler->is_configured()) {
        wp_send_json_error('API not configured.');
    }
    // Try to fetch tents as a test
    $tents = $handler->get_tents();
    if (is_array($tents) && count($tents) > 0) {
        wp_send_json_success('API connection successful!');
    } else {
        wp_send_json_error('API connection failed or returned no tents.');
    }
});

// Add AJAX handler for booking submission
add_action('wp_ajax_oktoberfest_submit_booking', 'oktoberfest_handle_booking_submission');
add_action('wp_ajax_nopriv_oktoberfest_submit_booking', 'oktoberfest_handle_booking_submission');

function oktoberfest_handle_booking_submission() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'oktoberfest_booking_nonce')) {
        wp_send_json_error('Security verification failed.');
        return;
    }
    
    // Sanitize form data
    $booking_data = [
        'selected_date' => sanitize_text_field($_POST['selected_date'] ?? ''),
        'attendees' => intval($_POST['attendees'] ?? 0),
        'session' => sanitize_text_field($_POST['session'] ?? ''),
        'tent_preference' => sanitize_text_field($_POST['tent_preference'] ?? ''),
        'selected_tent' => sanitize_text_field($_POST['selected_tent'] ?? ''),
        'first_name' => sanitize_text_field($_POST['first_name'] ?? ''),
        'last_name' => sanitize_text_field($_POST['last_name'] ?? ''),
        'email' => sanitize_email($_POST['email'] ?? ''),
        'phone' => sanitize_text_field($_POST['phone'] ?? ''),
        'company' => sanitize_text_field($_POST['company'] ?? ''),
        'message' => sanitize_textarea_field($_POST['message'] ?? '')
    ];
    
    // Basic validation
    $required_fields = ['selected_date', 'attendees', 'session', 'tent_preference', 'first_name', 'last_name', 'email', 'phone'];
    foreach ($required_fields as $field) {
        if (empty($booking_data[$field])) {
            wp_send_json_error("Missing required field: $field");
            return;
        }
    }
    
    // Validate email
    if (!is_email($booking_data['email'])) {
        wp_send_json_error('Invalid email address.');
        return;
    }
    
    // Use API handler to submit booking
    require_once plugin_dir_path(__FILE__) . 'includes/class-api-handler.php';
    $handler = \Everliz_Oktoberfest\API_Handler::instance();
    
    $result = $handler->submit_booking($booking_data);
    
    if ($result['success']) {
        // Get thank you page URL from settings
        $general_settings = get_option('oktoberfest_general_settings', []);
        $thank_you_page_id = $general_settings['thank_you_page'] ?? 0;
        $thank_you_url = $thank_you_page_id ? get_permalink($thank_you_page_id) : '';
        
        wp_send_json_success([
            'message' => 'Booking submitted successfully!',
            'redirect_url' => $thank_you_url
        ]);
    } else {
        wp_send_json_error($result['message'] ?? 'Booking submission failed.');
    }
}