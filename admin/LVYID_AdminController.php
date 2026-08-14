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
        $hook = add_menu_page('Вход через Яндекс', 'Вход через Яндекс ', 'manage_options', 'login_via_yandex', [$this, 'settingsPage'], plugin_dir_url(__FILE__) . '../public/plugin-icon.png', "79.8");
        add_action('admin_print_styles-' . $hook, [$this, 'enqueueAdminStyles']);
        add_action('admin_print_scripts-' . $hook, [$this, 'enqueueAdminScripts']);
    }

    public function enqueueAdminStyles()
    {
        wp_enqueue_style('login_via_yandex_font', 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap', [], null);
        $css_file = plugin_dir_path(__FILE__) . 'public/css/style.css';
        wp_enqueue_style('login_via_yandex_admin', plugins_url('public/css/style.css', __FILE__), ['login_via_yandex_font'], file_exists($css_file) ? filemtime($css_file) : '2.0.0');
    }

    public function enqueueAdminScripts()
    {
        $js_file = plugin_dir_path(__FILE__) . 'public/js/script.js';
        $user_id = get_current_user_id();
        $show_welcome = empty(get_user_meta($user_id, 'lvyid_v200_welcome_seen', true));

        wp_enqueue_script('login_via_yandex_admin', plugins_url('public/js/script.js', __FILE__), [], file_exists($js_file) ? filemtime($js_file) : '2.0.0', true);
        wp_add_inline_script('login_via_yandex_admin', 'const LVYID_Admin = ' . wp_json_encode([
                'ajax_url'     => admin_url('admin-ajax.php'),
                'nonce'        => wp_create_nonce('lvyid_admin_nonce'),
                'show_welcome' => $show_welcome,
            ]) . '; const REST_API_data = ' . wp_json_encode([
                'nonce' => wp_create_nonce('wp_rest'),
                'url'   => rest_url('login_via_yandex/update-settings'),
            ]) . ';', 'before');
    }

    public function settingsPage()
    {
        $options = LVYID_Options::getOptions();
        include plugin_dir_path(__FILE__) . 'public/index.php';
    }

    public static function ajaxDismissWelcome()
    {
        check_ajax_referer('lvyid_admin_nonce', 'nonce');
        $user_id = get_current_user_id();
        if ($user_id) {
            update_user_meta($user_id, 'lvyid_v200_welcome_seen', true);
        }
        wp_send_json_success();
    }

    public static function ajaxUpdateSettings($request_data = [])
    {
        if (empty($request_data)) {
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true);
            if (is_array($json)) {
                $request_data = $json;
            } else {
                $request_data = $_POST;
            }
        }

        $button = !empty($request_data['button']) && ($request_data['button'] === true || $request_data['button'] === 'true' || $request_data['button'] === '1');
        $container_id = isset($request_data['container_id']) ? sanitize_text_field($request_data['container_id']) : null;

        if ($button && empty($container_id)) {
            return wp_send_json_error('Заполните поле "ID - контейнера кнопки"');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'login_via_yandex_options';

        $widget = !empty($request_data['widget']) && ($request_data['widget'] === true || $request_data['widget'] === 'true' || $request_data['widget'] === '1');
        $alternative = !empty($request_data['alternative']) && ($request_data['alternative'] === true || $request_data['alternative'] === 'true' || $request_data['alternative'] === '1');
        $button_default = !empty($request_data['button_default']) && ($request_data['button_default'] === true || $request_data['button_default'] === 'true' || $request_data['button_default'] === '1');
        $copyright = !isset($request_data['copyright']) || ($request_data['copyright'] === true || $request_data['copyright'] === 'true' || $request_data['copyright'] === '1');
        $use_ajax_webhook = !empty($request_data['use_ajax_webhook']) && ($request_data['use_ajax_webhook'] === true || $request_data['use_ajax_webhook'] === 'true' || $request_data['use_ajax_webhook'] === '1');

        $data = [
            'client_id'        => isset($request_data['client_id']) ? trim(sanitize_text_field($request_data['client_id'])) : '',
            'client_secret'    => isset($request_data['client_secret']) ? trim(sanitize_text_field($request_data['client_secret'])) : '',
            'button'           => $button,
            'container_id'     => $container_id,
            'widget'           => $widget,
            'alternative'      => $alternative,
            'button_default'   => $button_default,
            'copyright'        => $copyright,
            'use_ajax_webhook' => $use_ajax_webhook,
        ];

        $upgrade = new LVYID_Upgrade();
        $upgrade->add_button_default_column();
        $upgrade->add_alternative_column();
        $upgrade->add_copyright_column();
        $upgrade->add_use_ajax_webhook_column();

        $result = $wpdb->insert($table_name, $data);

        if ($result) {
            return wp_send_json_success('Успешное сохранение данных');
        } else {
            return wp_send_json_error('Ошибка при сохранении данных');
        }
    }

    public static function updateSettings(WP_REST_Request $request)
    {
        return self::ajaxUpdateSettings($request->get_params());
    }
}
