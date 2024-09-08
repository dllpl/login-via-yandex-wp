<?php

require_once plugin_dir_path(__FILE__) . '../../includes/Options.php';

class PublicController
{
    use Options;

    private $options;

    public function __construct()
    {
        $options = Options::getOptions();
        $this->options = $options ?? null;
    }

    public function scriptInit()
    {
        $options = $this->options;

        if ($options && is_array($options) && !empty($options['client_id'] && !empty($options['client_secret']))) {

            wp_enqueue_style('login_via_yandex', plugins_url('../../public/login_via_yandex.css', __FILE__), [],
                filemtime(plugin_dir_path(__FILE__) . '../../public/login_via_yandex.css'), 'all');

            wp_enqueue_script('login_via_yandex', plugins_url('../../public/login_via_yandex.js', __FILE__), [],
                filemtime(plugin_dir_path(__FILE__) . '../../public/login_via_yandex.js'), 'in_footer');

            wp_add_inline_script('login_via_yandex', 'const yaWpData = ' . wp_json_encode([
                    'client_id' => $options['client_id'],
                    'container_id' => $options['container_id'],
                    'button' => $options['button'] ?? false,
                    'widget' => $options['widget'] ?? false,
                ]), 'before');


        } else {
            wp_add_inline_script('login_via_yandex',
                'const yaWpData = ' . wp_json_encode(['error' => 'Задайте настройки плагина Яндекс ID, чтобы начать работу']), 'before');
        }
    }
}
