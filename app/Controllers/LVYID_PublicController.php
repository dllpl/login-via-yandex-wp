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
                    'button_default'       => $options['button_default'] ?? false,
                    'button_view'          => $options['button_view'] ?? 'main',
                    'button_theme'         => $options['button_theme'] ?? 'light',
                    'button_size'          => $options['button_size'] ?? 'm',
                    'button_border_radius' => $options['button_border_radius'] ?? '8',
                    'button_icon'          => $options['button_icon'] ?? 'ya',
                    'woo_active'           => $woo_active,
                    'ajaxurl'              => admin_url('admin-ajax.php'),
                    'ajax_nonce'           => wp_create_nonce('lvyid_auth_nonce'),
                    'use_ajax_webhook'     => !empty($options['use_ajax_webhook']),
                    'redirect_uri'         => !empty($options['use_ajax_webhook'])
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
            echo '<div id="' . esc_attr($id) . '" class="lvyid_auth_button lvyid_auth_default"'
                . ' data-view="' . esc_attr($options['button_view'] ?? 'main') . '"'
                . ' data-theme="' . esc_attr($options['button_theme'] ?? 'light') . '"'
                . ' data-size="' . esc_attr($options['button_size'] ?? 'm') . '"'
                . ' data-radius="' . esc_attr($options['button_border_radius'] ?? '8') . '"'
                . ' data-icon="' . esc_attr($options['button_icon'] ?? 'ya') . '"></div>';
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

        $atts = shortcode_atts([
            'view'   => $options['button_view'] ?? 'main',
            'theme'  => $options['button_theme'] ?? 'light',
            'size'   => $options['button_size'] ?? 'm',
            'radius' => $options['button_border_radius'] ?? '8',
            'icon'   => $options['button_icon'] ?? 'ya',
        ], $atts, 'login_via_yandex');

        self::$button_counter++;
        $id = 'lvyid_auth_shortcode_' . self::$button_counter;
        return '<div id="' . esc_attr($id) . '" class="lvyid_auth_button lvyid_shortcode_button"'
            . ' data-view="' . esc_attr($atts['view']) . '"'
            . ' data-theme="' . esc_attr($atts['theme']) . '"'
            . ' data-size="' . esc_attr($atts['size']) . '"'
            . ' data-radius="' . esc_attr($atts['radius']) . '"'
            . ' data-icon="' . esc_attr($atts['icon']) . '"></div>';
    }
}
