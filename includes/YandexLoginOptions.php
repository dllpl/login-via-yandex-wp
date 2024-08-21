<?php

trait YandexLoginOptions
{
    public static function getOptions()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yandex_login_options';

        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM %s ORDER BY id DESC LIMIT 1", $table_name));

        if ($row) {
            return [
                'client_id' => $row->client_id,
                'client_secret' => $row->client_secret,
                'button' => $row->button,
                'container_id' => $row->container_id,
                'widget' => $row->widget,
            ];
        } else {
            return false;
        }
    }

    public function setOptions()
    {

    }
}
