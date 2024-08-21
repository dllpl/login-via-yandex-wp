<?php

require_once plugin_dir_path(__FILE__) . '../../includes/YandexLoginOptions.php';

class YandexLogin
{

    use YandexLoginOptions;

    private $login_url = 'https://login.yandex.ru/info?format=json';

    private $access_token_url = 'https://oauth.yandex.ru/token';

    private $options;

    public function __construct()
    {
        $options = YandexLoginOptions::getOptions();
        $this->options = $options ?? null;
    }


    public function getAccessToken($code)
    {
        if ($ch = curl_init()) {

            $options = $this->options;

            $grant_type = 'authorization_code';

            if (empty($options['client_id']) || empty($options['client_secret'])) {
                return false;
            }

            $url = $this->access_token_url;

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);

            $postData = [
                "grant_type" => $grant_type,
                "code" => $code,
                "client_id" => $options['client_id'],
                "client_secret" => $options['client_secret']
            ];

            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);

            $response = curl_exec($ch);
            curl_close($ch);

            if (curl_error($ch)) {
                return 'Ошибка cURL: ' . curl_error($ch);
            } else {
                return json_decode($response, true);
            }
        } else {
            throw new Exception('cURL - не установлен');
        }
    }

    /**
     * Запрашиваем данные пользователя
     *
     */
    public function getInfo($oauth_token)
    {
        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $this->login_url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Authorization:' . 'oAuth ' . $oauth_token
            ]);

            $out = curl_exec($curl);
            curl_close($curl);

            $result = json_decode($out);

            if (isset($result->default_email)) {
                return $result;
            }

            return null;

        } else {
            throw new Exception('cURL - не установлен');
        }
    }
}
