<?php

require_once plugin_dir_path(__FILE__) . '../../includes/YandexLoginOptions.php';

class YandexLoginPublicController
{
    use YandexLoginOptions;

    private $options;

    public function __construct()
    {
        $options = YandexLoginOptions::getOptions();
        $this->options = $options ?? null;
    }

    public function scriptInit()
    {
        $options = $this->options;

        if ($options && is_array($options) && !empty($options['client_id'] && !empty($options['client_secret']))) {

            wp_enqueue_script('yandex_login', plugins_url('../../public/button_and_widget.js', __FILE__), [],
                filemtime(plugin_dir_path(__FILE__) . '../../public/button_and_widget.js'), 'in_footer');

            wp_add_inline_script('yandex_login', 'const yaWpData = ' . wp_json_encode($options), 'before');


        } else {
            wp_add_inline_script('yandex_login',
                'const yaWpData = ' . wp_json_encode(['error' => 'Задайте настройки плагина Яндекс ID, чтобы начать работу']), 'before');
        }
    }
}
