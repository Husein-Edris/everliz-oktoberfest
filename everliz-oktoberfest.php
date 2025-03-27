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

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'init']);
        // Add this line to enqueue assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function init()
    {
        // Check if Elementor is installed and activated
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-warning is-dismissible">';
                echo '<p>Elementor is required for the Oktoberfest VIP Booking System.</p>';
                echo '</div>';
            });
            return;
        }

        // Register widgets
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
    }

    /**
     * Enqueue plugin assets (CSS and JS)
     */
    public function enqueue_assets()
    {
        // Get file modification time for cache busting
        $css_file = plugin_dir_path(__FILE__) . 'assets/css/main.css';
        $css_version = file_exists($css_file) ? filemtime($css_file) : '1.0.0';

        // Enqueue the main stylesheet
        wp_enqueue_style(
            'oktoberfest-vip-styles',
            plugin_dir_url(__FILE__) . 'assets/css/main.css',
            [],
            $css_version
        );

        wp_enqueue_script(
            'oktoberfest-vip-scripts',
            plugin_dir_url(__FILE__) . 'assets/js/main.js',
            ['jquery'],
            $css_version,
            true
        );

        wp_enqueue_script(
            'oktoberfest-calendar',
            plugin_dir_url(__FILE__) . 'assets/js/calendar.js',
            ['jquery'],
            filemtime(plugin_dir_path(__FILE__) . 'assets/js/calendar.js'),
            true
        );
    }

    public function register_widgets($widgets_manager)
    {
        // Include widget files
        require_once(__DIR__ . '/widgets/reservation-form-widget.php');
        require_once(__DIR__ . '/widgets/booking-form-widget.php');

        // Register widgets
        $widgets_manager->register(new \Everliz_Oktoberfest\Reservation_Form_Widget());
        $widgets_manager->register(new \Everliz_Oktoberfest\Booking_Form_Widget());
    }
}

// Initialize the plugin
Oktoberfest_VIP_Booking::instance();