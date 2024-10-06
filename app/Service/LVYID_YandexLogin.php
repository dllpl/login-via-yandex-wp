<?php
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . '../../includes/LVYID_Options.php';

class LVYID_YandexLogin
{

    use LVYID_Options;

    private $login_url = 'https://login.yandex.ru/info?format=json';

    private $access_token_url = 'https://oauth.yandex.ru/token';

    private $options;

    public function __construct()
    {
        $options = LVYID_Options::getOptions();
        $this->options = $options ?? null;
    }


    public function getAccessToken($code)
    {
        $options = $this->options;

        $grant_type = 'authorization_code';

        if (empty($options['client_id']) || empty($options['client_secret'])) {
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

        if (is_wp_error($response)) {
            return 'Ошибка: ' . $response->get_error_message();
        } else {
            $response_body = wp_remote_retrieve_body($response);
            return json_decode($response_body, true);
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
