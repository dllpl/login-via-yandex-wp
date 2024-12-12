<?php
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . '../includes/LVYID_Options.php';
require_once plugin_dir_path(__FILE__) . '../includes/LVYID_Upgrade.php';

class LVYID_AdminController
{
    use LVYID_Options;

    private $options;

    public function __construct()
    {
        $options = LVYID_Options::getOptions();
        $this->options = $options ?? null;
    }

    public function addMenu()
    {
        add_menu_page('Вход через Яндекс', 'Вход через Яндекс ', 'manage_options', 'login_via_yandex', [$this, 'settingsPage'], plugin_dir_url(__FILE__) . '../public/plugin-icon.png', "79.8");
    }

    public function settingsPage()
    {
        $options = $this->options;

        wp_enqueue_style('login_via_yandex_admin', plugins_url('public/css/style.css', __FILE__), [], '1.0.6');
        include plugin_dir_path(__FILE__) . 'public/index.php';
        wp_enqueue_script('login_via_yandex_admin', plugins_url('public/js/script.js', __FILE__), [], '1.0.6', true);
        wp_add_inline_script('login_via_yandex_admin', 'const REST_API_data = ' . wp_json_encode([
                'nonce' => wp_create_nonce('wp_rest'),
            ]), 'before');
    }

    public static function updateSettings(WP_REST_Request $request)
    {
        if (!empty($request['button']) && empty($request['container_id'])) {
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
            'alternative' => $request['alternative'] ?? null,
            'button_default' => $request['button_default'] ?? null
        ];

        $upgrade = new LVYID_Upgrade();
        $upgrade->add_button_default_column();
        $upgrade->add_alternative_column();

        $result = $wpdb->insert($table_name, $data);

        if ($result) {
            return wp_send_json_success('Успешное сохранение данных');
        } else {
            return wp_send_json_error('Ошибка при сохранении данных');
        }
    }
}
