<?php
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . '../../includes/LVYID_Options.php';
require_once plugin_dir_path(__FILE__) . '../../app/LVYID_Logger.php';

class LVYID_YandexLogin
{

    use LVYID_Options;

    private $login_url = 'https://login.yandex.ru/info?format=json';

    private $access_token_url = 'https://oauth.yandex.ru/token';

    private $options;

    private $log_class;

    public function __construct()
    {
        $options = LVYID_Options::getOptions();
        $this->options = $options ?? null;
        $this->log_class = new LVYID_Logger();
    }


    public function getAccessToken($code)
    {
        $options = $this->options;

        $grant_type = 'authorization_code';

        if (empty($options['client_id']) || empty($options['client_secret'])) {
            $this->log_class->error('not set client_secret or client_id');
            return false;
        }

        $url = $this->access_token_url;

        $args = [
            'body' => [
                'grant_type' => $grant_type,
                'code' => $code,
                'client_id' => $options['client_id'],
                'client_secret' => $options['client_secret']
            ],
            'timeout' => 15,
            'blocking' => true,
            'headers' => [],
        ];

        $response = wp_remote_post($url, $args);

        if ($response['response']['code'] !== 200) {
            $this->log_class->error('getAccessToken error: ' . $response['body']);
            return ['status' => false, 'error' => json_decode($response['body'])];
        } else {
            $response_body = wp_remote_retrieve_body($response);
            return ['status' => true, 'access_token' => json_decode($response_body, true)['access_token']];
        }
    }

    /**
     * Запрашиваем данные пользователя
     *
     */
    public function getInfo($oauth_token)
    {
        $args = [
            'headers' => [
                'Authorization' => 'oAuth ' . $oauth_token,
            ],
            'sslverify' => false,
            'timeout' => 15,
        ];

        $response = wp_remote_get($this->login_url, $args);

        if (is_wp_error($response)) {
            throw new Exception('Ошибка: ' . sprintf('%s', esc_html($response->get_error_message())));
        }

        $response_body = wp_remote_retrieve_body($response);
        $result = json_decode($response_body);

        if (isset($result->default_email)) {
            return $result;
        }

        return null;
    }
}
