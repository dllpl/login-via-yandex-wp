<?php

class Uninstall
{

    public static function make()
    {
        global $wpdb;
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'yandex_login_options');
    }
}
