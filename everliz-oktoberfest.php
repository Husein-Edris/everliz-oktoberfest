<?php

/**
 * Plugin Name: Oktoberfest VIP Booking
 * Description: Custom Elementor widgets for Oktoberfest VIP booking
 * Version: 1.5
 * Author: Edris Husein
 * Author URI: edrishusein.com
 */
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
            ['jquery'],
            filemtime($this->plugin_path . 'assets/js/main.js'),
            true
        );
    }

    public function register_widgets($widgets_manager)
    {
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
        // General Settings
        register_setting('oktoberfest_settings', 'oktoberfest_general_settings');

        // Date Ranges Settings
        register_setting('oktoberfest_settings', 'oktoberfest_date_ranges');

        // API Settings
        register_setting('oktoberfest_settings', 'oktoberfest_api_settings', [
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

            <form method="post" action="options.php" class="oktoberfest-form">
                <?php
                settings_fields('oktoberfest_settings');

                if ($active_tab == 'general') {
                    $this->render_general_tab();
                } elseif ($active_tab == 'date_ranges') {
                    $this->render_date_ranges_tab();
                } else {
                    $this->render_api_settings_tab();
                }

                submit_button('Save Settings');
                ?>
            </form>
        </div>
    <?php
    }

    private function render_general_tab()
    {
        $general_settings = get_option('oktoberfest_general_settings', []);
    ?>
        <div class="general-settings-container">
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
            </table>
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
            <div class="date-ranges-header">
                <h3>Event Date Ranges</h3>
                <p class="description">Set the date ranges for each year's Oktoberfest event.</p>
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
        </div>
    <?php
    }

    private function render_api_settings_tab()
    {
        $api_settings = get_option('oktoberfest_api_settings', []);
    ?>
        <div class="api-settings-container">
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
        </div>
<?php
    }
}

// Initialize the plugin
Oktoberfest_VIP_Booking::instance();
