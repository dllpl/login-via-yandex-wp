<?php
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . '../../includes/LVYID_Options.php';

class LVYID_PublicController
{
    use LVYID_Options;

    private $options;
    private static $button_counter = 0;

    public function __construct()
    {
        $options = LVYID_Options::getOptions();
        $this->options = $options ?? null;
    }

    public function scriptInit()
    {
        $options = $this->options;

        if ($options && is_array($options) && !empty($options['client_id']) && !empty($options['client_secret'])) {

            if(function_exists('is_plugin_active')) {
                $woo_active = (bool)is_plugin_active('woocommerce/woocommerce.php');
            } else {
                $woo_active = false;
            }

            wp_enqueue_script('login_via_yandex', plugins_url('../../public/login_via_yandex.js', __FILE__), [],
                filemtime(plugin_dir_path(__FILE__) . '../../public/login_via_yandex.js'), 'in_footer');

            wp_add_inline_script('login_via_yandex', 'const yaWpData = ' . wp_json_encode([
                    'client_id'      => $options['client_id'],
                    'container_id'   => $options['container_id'],
                    'button'         => $options['button'] ?? false,
                    'widget'         => $options['widget'] ?? false,
                    'alternative'    => $options['alternative'] ?? false,
                    'button_default' => $options['button_default'] ?? false,
                    'woo_active'       => $woo_active,
                    'ajaxurl'          => admin_url('admin-ajax.php'),
                    'ajax_nonce'       => wp_create_nonce('lvyid_auth_nonce'),
                    'use_ajax_webhook' => !empty($options['use_ajax_webhook']),
                    'redirect_uri'     => !empty($options['use_ajax_webhook'])
                        ? (admin_url('admin-ajax.php') . '?action=lvyid_webhook')
                        : home_url('/wp-json/login_via_yandex/webhook'),
                ]), 'before');

        } else {
            wp_add_inline_script('login_via_yandex',
                'const yaWpData = ' . wp_json_encode(['error' => 'Задайте настройки плагина Яндекс ID, чтобы начать работу']), 'before');
        }
    }

    public function styleInit()
    {
        wp_enqueue_style('login_via_yandex', plugins_url('../../public/login_via_yandex.css', __FILE__), [],
            filemtime(plugin_dir_path(__FILE__) . '../../public/login_via_yandex.css'), 'all');
    }

    public function defaultAuthButtonsInit()
    {
        $options = $this->options;

        if ($options && is_array($options) && !empty($options['client_id']) && !empty($options['client_secret']) && !empty($options['button_default'])) {
            self::$button_counter++;
            $id = 'lvyid_auth_default_' . self::$button_counter;
            echo '<div id="' . esc_attr($id) . '" class="lvyid_auth_button lvyid_auth_default"></div>';
        }
    }

    public static function shortcodeButton($atts = [])
    {
        if (is_user_logged_in()) {
            return '';
        }

        $options = LVYID_Options::getOptions();
        if (!$options || empty($options['client_id']) || empty($options['client_secret'])) {
            return '';
        }

        self::$button_counter++;
        $id = 'lvyid_auth_shortcode_' . self::$button_counter;
        return '<div id="' . esc_attr($id) . '" class="lvyid_auth_button lvyid_shortcode_button"></div>';
    }
}
