<?php

/**
 * API Handler Class
 * 
 * Manages communication with the external API for the Oktoberfest VIP booking system.
 */

namespace Oktoberfest_VIP;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class API_Handler
{
    /**
     * @var API_Handler Singleton instance
     */
    private static $instance = null;

    /**
     * @var string API base URL
     */
    private $api_base_url;

    /**
     * @var string API key
     */
    private $api_key;

    /**
     * Get singleton instance
     * 
     * @return API_Handler
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $options = get_option('oktoberfest_vip_settings', []);
        $this->api_base_url = isset($options['api_url']) ? rtrim($options['api_url'], '/') : '';
        $this->api_key = isset($options['api_key']) ? $options['api_key'] : '';
    }

    /**
     * Check if API is configured
     * 
     * @return bool
     */
    public function is_configured()
    {
        return !empty($this->api_base_url) && !empty($this->api_key);
    }

    /**
     * Get available locations
     * 
     * @return array
     */
    public function get_locations()
    {
        return $this->make_request('GET', '/locations');
    }

    /**
     * Get available sessions for a date
     * 
     * @param string $date Date in Y-m-d format
     * @return array
     */
    public function get_sessions($date)
    {
        return $this->make_request('GET', '/sessions', [
            'date' => $date
        ]);
    }

    /**
     * Get attendee options
     * 
     * @return array
     */
    public function get_attendee_options()
    {
        return $this->make_request('GET', '/attendees');
    }

    /**
     * Get tents from API or dummy data
     *
     * @return array
     */
    public function get_tents()
    {
        if ($this->is_configured()) {
            $url = rtrim($this->api_base_url, '/') . '/tents';
            $args = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Accept' => 'application/json',
                ]
            ];
            $response = wp_remote_get($url, $args);
            if (!is_wp_error($response)) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                if (is_array($data)) {
                    // Normalize API response if needed
                    return $data;
                }
            }
        }
        // Fallback to dummy data
        return self::get_local_tents();
    }

    /**
     * Get seasons (date ranges) from API or dummy data
     *
     * @return array
     */
    public function get_seasons()
    {
        if ($this->is_configured()) {
            $url = rtrim($this->api_base_url, '/') . '/seasons';
            $args = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Accept' => 'application/json',
                ]
            ];
            $response = wp_remote_get($url, $args);
            if (!is_wp_error($response)) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                if (is_array($data)) {
                    return $data;
                }
            }
        }
        // Fallback to WP options or dummy
        $date_ranges = get_option('oktoberfest_date_ranges');
        if (!is_array($date_ranges)) {
            $date_ranges = [[
                'year' => '2025',
                'start_date' => '2025-09-20',
                'end_date' => '2025-10-05'
            ]];
        }
        return $date_ranges;
    }

    /**
     * Submit booking
     * 
     * @param array $booking_data Booking data
     * @return array
     */
    public function submit_booking($booking_data)
    {
        return $this->make_request('POST', '/bookings', $booking_data);
    }

    /**
     * API request
     * 
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $endpoint API endpoint
     * @param array $params Parameters
     * @return array Response data
     */
    private function make_request($method, $endpoint, $params = [])
    {
        // Use dummy data if API is not configured
        if (!$this->is_configured()) {
            return $this->get_dummy_data($endpoint, $params);
        }

        $url = $this->api_base_url . $endpoint;

        $args = [
            'method' => $method,
            'timeout' => 30,
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking' => true,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ];

        if ('GET' === $method && !empty($params)) {
            $url = add_query_arg($params, $url);
        } elseif ('POST' === $method && !empty($params)) {
            $args['body'] = json_encode($params);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            error_log('Oktoberfest VIP API Error: ' . $response->get_error_message());
            return [
                'success' => false,
                'message' => $response->get_error_message()
            ];
        }

        $response_code = wp_remote_retrieve_response_code($response);

        if ($response_code < 200 || $response_code >= 300) {
            error_log('Oktoberfest VIP API Error: ' . $response_code);
            return [
                'success' => false,
                'message' => 'API returned error code: ' . $response_code
            ];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!$data) {
            error_log('Oktoberfest VIP API Error: Invalid JSON response');
            return [
                'success' => false,
                'message' => 'Invalid API response'
            ];
        }

        return [
            'success' => true,
            'data' => $data
        ];
    }

    /**
     * Dummy data for development
     * 
     * @param string $endpoint API endpoint
     * @param array $params Parameters
     * @return array Dummy data
     */
    private function get_dummy_data($endpoint, $params = [])
    {
        $dummy_data = [
            '/locations' => [
                'success' => true,
                'data' => [
                    ['id' => 'munich', 'name' => 'Munich Central'],
                    ['id' => 'bavaria', 'name' => 'Bavaria Ground'],
                    ['id' => 'any', 'name' => 'Any Location']
                ]
            ],
            '/sessions' => [
                'success' => true,
                'data' => [
                    ['id' => 'day', 'name' => 'Day session (approx. 8 am – 4 pm)'],
                    ['id' => 'evening', 'name' => 'Evening session (approx. 5 pm – 11 pm)']
                ]
            ],
            '/attendees' => [
                'success' => true,
                'data' => [1, 2, 3, 4, 5, 6, 8, 10, 12, 15, 20]
            ],
            '/tents' => [
                'success' => true,
                'data' => [
                    [
                        'id' => 'armbrustschutzenzelt',
                        'name' => 'Armbrustschützenzelt',
                        'capacity' => 5000,
                        'image' => 'armbrustschutzenzelt.jpg',
                        'description' => 'Traditional tent with crossbow shooting competition.'
                    ],
                    [
                        'id' => 'augustiner',
                        'name' => 'Augustiner-Festhalle',
                        'capacity' => 6000,
                        'image' => 'augustiner.jpg',
                        'description' => 'Famous for its Augustiner beer served from wooden barrels.'
                    ],
                    [
                        'id' => 'fischer-vroni',
                        'name' => 'Fischer-Vroni',
                        'capacity' => 3000,
                        'image' => 'fischer-vroni.jpg',
                        'description' => 'Known for its fish specialties including "Steckerlfisch".'
                    ],
                    [
                        'id' => 'hacker-festzelt',
                        'name' => 'Hacker-Festzelt',
                        'capacity' => 7000,
                        'image' => 'hacker-festzelt.jpg',
                        'description' => 'Also known as "Himmel der Bayern" (Heaven of Bavarians).'
                    ],
                    [
                        'id' => 'hofbrau',
                        'name' => 'Hofbräu-Festzelt',
                        'capacity' => 6000,
                        'image' => 'hofbrau.jpg',
                        'description' => 'Popular with international visitors and known for party atmosphere.'
                    ],
                    [
                        'id' => 'kafer-wiesn-schanke',
                        'name' => 'Käfer Wiesn-Schänke',
                        'capacity' => 3000,
                        'image' => 'kafer-wiesn-schanke.jpg',
                        'description' => 'Upscale tent popular with celebrities and VIPs.'
                    ]
                ]
            ],
            '/bookings' => [
                'success' => true,
                'data' => [
                    'booking_id' => 'BK' . rand(10000, 99999),
                    'status' => 'pending',
                    'message' => 'Booking received successfully'
                ]
            ]
        ];

        // Return dummy data for the specified endpoint
        if (isset($dummy_data[$endpoint])) {
            return $dummy_data[$endpoint];
        }

        // Default dummy response
        return [
            'success' => false,
            'message' => 'Endpoint not found in dummy data'
        ];
    }

    /**
     * Deprecated: Use get_tents() instead.
     *
     * @return array
     */
    public static function get_local_tents()
    {
        $base_url = plugin_dir_url(__FILE__) . '../assets/images/';
        $base_url = str_replace('includes/', '', $base_url); // Normalize path if needed
        return [
            [
                'id' => 'armbrustschutzenzelt',
                'name' => 'Armbrustschützenzelt',
                'image' => $base_url . 'tent1.jpg'
            ],
            [
                'id' => 'augustiner',
                'name' => 'Augustiner-Festhalle',
                'image' => $base_url . 'tent2.jpg'
            ],
            [
                'id' => 'fischer-vroni',
                'name' => 'Fischer-Vroni',
                'image' => $base_url . 'tent3.jpg'
            ],
            [
                'id' => 'hacker-festzelt',
                'name' => 'Hacker-Festzelt',
                'image' => $base_url . 'tent1.jpg'
            ],
            [
                'id' => 'hofbrau',
                'name' => 'Hofbräu-Festzelt',
                'image' => $base_url . 'tent2.jpg'
            ],
            [
                'id' => 'kafer-wiesn-schanke',
                'name' => 'Käfer Wiesn-Schänke',
                'image' => $base_url . 'tent3.jpg'
            ]
        ];
    }
}
