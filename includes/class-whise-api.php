<?php
if (!defined('ABSPATH')) exit;

class Whise_API {
    private $endpoint;
    private $timeout = 15;
    private $cache_ttl = 3600; // 1h

    public function __construct($endpoint = '') {
        $this->endpoint = $endpoint ?: get_option('whise_api_endpoint', 'https://api.whise.eu/');
    }

    /**
     * Récupère le client token (authentification Public API)
     */
    public function get_client_token() {
        $transient_key = 'whise_client_token';
        $token = get_transient($transient_key);
        if ($token) return $token;

        // 1. Authentification Marketplace
        $username = get_option('whise_api_username', '');
        $password = get_option('whise_api_password', '');
        $client_id = get_option('whise_client_id', '');
        $office_id = get_option('whise_office_id', '');
        if (!$username || !$password || !$client_id || !$office_id) return false;

        $marketplace_token = $this->get_marketplace_token($username, $password);
        if (!$marketplace_token) return false;

        // 2. Récupération du client token
        $url = rtrim($this->endpoint, '/') . '/v1/admin/clients/token';
        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $marketplace_token,
                'Content-Type' => 'application/json',
            ],
            'timeout' => $this->timeout,
            'body' => json_encode([
                'clientId' => $client_id,
                'officeId' => $office_id
            ]),
        ]);
        if (is_wp_error($response)) return false;
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (empty($data['token'])) return false;
        set_transient($transient_key, $data['token'], 3500); // ~1h
        return $data['token'];
    }

    /**
     * Récupère le token Marketplace (OAuth2)
     */
    private function get_marketplace_token($username, $password) {
        $transient_key = 'whise_marketplace_token';
        $token = get_transient($transient_key);
        if ($token) return $token;
        $url = rtrim($this->endpoint, '/') . '/token';
        $response = wp_remote_post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'timeout' => $this->timeout,
            'body' => json_encode([
                'username' => $username,
                'password' => $password
            ]),
        ]);
        if (is_wp_error($response)) return false;
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (empty($data['token'])) return false;
        set_transient($transient_key, $data['token'], 3500); // ~1h
        return $data['token'];
    }

    /**
     * Effectue une requête POST à l'API Whise (Public API) avec client token
     */
    public function post($path, $body = []) {
        $token = $this->get_client_token();
        if (!$token) return false;
        $url = rtrim($this->endpoint, '/') . '/' . ltrim($path, '/');
        $response = wp_remote_post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'timeout' => $this->timeout,
            'body' => !empty($body) ? json_encode($body) : null,
        ]);
        if (is_wp_error($response)) return false;
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        if ($code !== 200) return false;
        return json_decode($body, true);
    }

    /**
     * Effectue une requête GET à l'API Whise (Public API) avec client token
     */
    public function get($path, $args = []) {
        $token = $this->get_client_token();
        if (!$token) return false;
        $url = rtrim($this->endpoint, '/') . '/' . ltrim($path, '/');
        if (!empty($args)) {
            $url .= '?' . http_build_query($args);
        }
        $response = wp_remote_get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
            'timeout' => $this->timeout,
        ]);
        if (is_wp_error($response)) return false;
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        if ($code !== 200) return false;
        return json_decode($body, true);
    }
}
