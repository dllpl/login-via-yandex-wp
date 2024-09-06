<?php

require_once plugin_dir_path(__FILE__) . '../includes/Options.php';

class AdminController
{
    use Options;

    private $options;

    public function __construct()
    {
        $options = Options::getOptions();
        $this->options = $options ?? null;
    }

    public function addMenu()
    {
        add_options_page('Вход через Яндекс', 'Вход через Яндекс', 'manage_options', 'login_via_yandex', [$this, 'settingsPage']);
    }

    public function settingsPage()
    {
        $options = $this->options;
        include plugin_dir_path(__FILE__) . 'public/index.php';
    }

    public static function updateSettings(WP_REST_Request $request)
    {
        if(!empty($request['button']) && empty($request['container_id'])) {
            return wp_send_json_error('Заполните поле "ID - контейнера кнопки"');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'login_via_yandex_options';

        $data = [
            'client_id' => isset($request['client_id']) ? trim(sanitize_text_field($request['client_id'])) : '',
            'client_secret' => isset($request['client_secret']) ? trim(sanitize_text_field($request['client_secret'])) : '',
            'button' => $request['button'] ?? null,
            'container_id' => isset($request['container_id']) ? sanitize_text_field($request['container_id']) : null,
            'widget' => $request['widget'] ?? null,
        ];

        $result = $wpdb->insert($table_name, $data);

        if ($result) {
            return wp_send_json_success('Успешное сохранение данных');
        } else {
            return wp_send_json_error('Ошибка при сохранении данных');
        }
    }
}
