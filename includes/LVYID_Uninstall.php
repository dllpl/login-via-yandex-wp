<?php
if (!defined('ABSPATH')) exit;
class LVYID_Uninstall
{

    public static function make()
    {
        global $wpdb;
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'login_via_yandex_options');
    }
}
