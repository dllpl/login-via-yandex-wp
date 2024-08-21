<?php

class YandexLoginUninstall
{

    public static function make()
    {
        global $wpdb;
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'yandexid_webseed_options');
    }
}
