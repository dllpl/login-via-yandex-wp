<?php
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . '../../includes/LVYID_Options.php';

class LVYID_PublicController
{
    use LVYID_Options;

    private $options;

    public function __construct()
    {
        $options = LVYID_Options::getOptions();
        $this->options = $options ?? null;
    }

    public function scriptInit()
    {
        $options = $this->options;

        if ($options && is_array($options) && !empty($options['client_id'] && !empty($options['client_secret']))) {

            wp_enqueue_script('login_via_yandex', plugins_url('../../public/login_via_yandex.js', __FILE__), [],
                filemtime(plugin_dir_path(__FILE__) . '../../public/login_via_yandex.js'), 'in_footer');

            wp_add_inline_script('login_via_yandex', 'const yaWpData = ' . wp_json_encode([
                    'client_id' => $options['client_id'],
                    'container_id' => $options['container_id'],
                    'button' => $options['button'] ?? false,
                    'widget' => $options['widget'] ?? false,
                    'alternative' => $options['alternative'] ?? false,
                    'button_default' => $options['button_default'] ?? false
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

        if ($options && is_array($options) && !empty($options['client_id'] && !empty($options['client_secret'])) && $options['button_default']) {
            echo '<div id="lvyid_auth_default"></div>';
        }
    }
}
