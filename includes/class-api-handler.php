<?php

namespace Everliz_Oktoberfest;

class Api_Handler
{
    private static $instance = null;
    private $api_base_url;
    private $api_key;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $options = get_option('Everliz_Oktoberfest');
        $this->api_base_url = isset($options['api_url']) ? $options['api_url'] : '';
        $this->api_key = isset($options['api_key']) ? $options['api_key'] : '';
    }

    public function get_locations()
    {
        // Will be replaced with API call
        return $this->make_request('GET', '/locations');
    }

    public function get_available_tents($location, $date)
    {
        return $this->make_request('GET', '/tents', [
            'location' => $location,
            'date' => $date
        ]);
    }

    private function make_request($method, $endpoint, $params = [])
    {
        if (!$this->api_base_url || !$this->api_key) {
            return $this->get_dummy_data($endpoint);
        }

        // Real API call implementation here
        return [];
    }

    private function get_dummy_data($endpoint)
    {
        $dummy_data = [
            '/locations' => [
                ['id' => 'munich', 'name' => 'Munich Central'],
                ['id' => 'bavaria', 'name' => 'Bavaria Ground'],
                ['id' => 'stuttgart', 'name' => 'Stuttgart Field']
            ],
            '/tables' => [
                
                    'id' => 'vip1',
                    'name' => 'VIP Evening Table',
                    'capacity' => 10,
                    'time_slot' => '18:00-23:00',
                    'includes' => ['Reserved seating', 'Personal guide', 'Welcome drinks']
                ],
            '/tables' => [
                
                    'id' => 'vip2',
                    'name' => 'VIP Evening Table 2',
                    'capacity' => 10,
                    'time_slot' => '18:00-23:00',
                    'includes' => ['Reserved seating', 'Personal guide', 'Welcome drinks']
                ],
        ];

        return $dummy_data[$endpoint] ?? [];
    }
}