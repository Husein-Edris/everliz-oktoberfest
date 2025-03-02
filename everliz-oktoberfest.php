<?php

/**
 * Plugin Name: Everliz Oktoberfest Reservations
 * Description: Custom Elementor widget for Oktoberfest VIP table reservations
 * Version: 1.0.1
 * Author: Edris Husein
 */

if (!defined('ABSPATH')) exit;

final class Everliz_Oktoberfest
{
    const VERSION = '1.0.0';
    const MINIMUM_ELEMENTOR_VERSION = '3.0.0';
    const MINIMUM_PHP_VERSION = '7.0';

    private static $_instance = null;

    public static function instance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct()
    {
        $this->init_plugin();
    }

    private function init_plugin()
    {
        error_log('Everliz: Plugin initialization started');
        add_action('init', [$this, 'register_post_type']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_menu', [$this, 'add_plugin_page']);
        add_action('admin_init', [$this, 'page_init']);
        add_shortcode('tent_results', [$this, 'render_results_shortcode']);

        // Change to use elementor/init for better timing
        add_action('elementor/init', [$this, 'init']);
    }

    public function init()
    {
        error_log('Everliz: Init method called');

        if (!did_action('elementor/loaded')) {
            error_log('Everliz: Elementor not loaded');
            add_action('admin_notices', [$this, 'admin_notice_missing_elementor']);
            return;
        }

        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            error_log('Everliz: PHP version check failed');
            add_action('admin_notices', [$this, 'admin_notice_minimum_php_version']);
            return;
        }

        // Register widget
        add_action('elementor/widgets/register', [$this, 'init_widgets']);

        // Add form submission handling
        add_action('wp_ajax_everliz_submit_form', [$this, 'handle_form_submission']);
        add_action('wp_ajax_nopriv_everliz_submit_form', [$this, 'handle_form_submission']);
    }

    public function init_widgets()
    {
        error_log('Everliz: Initializing widgets');
        if (file_exists(__DIR__ . '/widgets/reservation-form-widget.php')) {
            error_log('Everliz: Widget file found');
            require_once(__DIR__ . '/widgets/reservation-form-widget.php');
            \Elementor\Plugin::instance()->widgets_manager->register(new \Everliz_Oktoberfest\Reservation_Form_Widget());
            error_log('Everliz: Widget registered');
        } else {
            error_log('Everliz: Widget file not found at: ' . __DIR__ . '/widgets/reservation-form-widget.php');
        }
    }

    public function add_plugin_page()
    {
        add_options_page(
            'Everliz VIP Reservations',
            'Everliz VIP',
            'manage_options',
            'everliz-settings',
            [$this, 'create_admin_page']
        );
    }

    public function create_admin_page()
    {
?>
        <div class="wrap">
            <h2>Everliz VIP Reservations Settings</h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('everliz_options');
                do_settings_sections('everliz-settings');
                submit_button();
                ?>
            </form>
        </div>
<?php
    }

    public function page_init()
    {
        register_setting(
            'everliz_options',
            'everliz_api_settings',
            [$this, 'sanitize']
        );

        add_settings_section(
            'everliz_api_section',
            'API Settings',
            [$this, 'section_info'],
            'everliz-settings'
        );

        add_settings_field(
            'api_url',
            'API URL',
            [$this, 'api_url_callback'],
            'everliz-settings',
            'everliz_api_section'
        );

        add_settings_field(
            'api_key',
            'API Key',
            [$this, 'api_key_callback'],
            'everliz-settings',
            'everliz_api_section'
        );
    }

    public function sanitize($input)
    {
        $new_input = array();

        if (isset($input['api_url']))
            $new_input['api_url'] = sanitize_text_field($input['api_url']);
        if (isset($input['api_key']))
            $new_input['api_key'] = sanitize_text_field($input['api_key']);

        return $new_input;
    }

    public function section_info()
    {
        print 'Enter your API settings below:';
    }

    public function api_url_callback()
    {
        $options = get_option('everliz_api_settings');
        printf(
            '<input type="text" id="api_url" name="everliz_api_settings[api_url]" value="%s" class="regular-text" />',
            isset($options['api_url']) ? esc_attr($options['api_url']) : ''
        );
    }

    public function api_key_callback()
    {
        $options = get_option('everliz_api_settings');
        printf(
            '<input type="password" id="api_key" name="everliz_api_settings[api_key]" value="%s" class="regular-text" />',
            isset($options['api_key']) ? esc_attr($options['api_key']) : ''
        );
    }


    public function register_post_type()
    {
        register_post_type('tent_results', [
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => false,
            'show_in_menu' => false,
            'rewrite' => ['slug' => 'tent-results'],
        ]);
    }

    public function render_results_shortcode()
    {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/results-template.php';
        return ob_get_clean();
    }

    public function enqueue_assets()
    {
        wp_enqueue_style('everliz-oktoberfest', plugins_url('assets/css/reservation-form.css', __FILE__));
        wp_enqueue_script('everliz-oktoberfest', plugins_url('assets/js/reservation-form.js', __FILE__), ['jquery'], self::VERSION, true);
    }

    public function admin_notice_minimum_php_version()
    {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p>Everliz VIP Reservations requires PHP version ' . self::MINIMUM_PHP_VERSION . '+</p>';
        echo '</div>';
    }

    public function admin_notice_missing_elementor()
    {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p>Everliz VIP Reservations requires Elementor to be installed and activated.</p>';
        echo '</div>';
    }
}

// Initialize the plugin
add_action('plugins_loaded', function () {
    Everliz_Oktoberfest::instance();
});

// Create directory structure
$dirs = ['widgets', 'templates', 'assets/css', 'assets/js', 'assets/images'];
foreach ($dirs as $dir) {
    $path = plugin_dir_path(__FILE__) . $dir;
    if (!file_exists($path)) {
        mkdir($path, 0755, true);
    }
}
