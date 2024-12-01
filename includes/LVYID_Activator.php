<?php
if (!defined('ABSPATH')) exit;

class LVYID_Activator
{
    /**
     * @return void
     */
    public static function make()
    {
        global $wpdb;
        $table_options = $wpdb->prefix . 'login_via_yandex_options';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = ["
            CREATE TABLE $table_options (
            `id` INT NOT NULL AUTO_INCREMENT,
            `client_id` VARCHAR(32) NOT NULL,
            `client_secret` VARCHAR(32) NOT NULL,
            `button` BOOLEAN DEFAULT NULL,
            `container_id` VARCHAR(100) DEFAULT NULL,
            `widget` BOOLEAN DEFAULT NULL,
            `alternative` BOOLEAN DEFAULT FALSE,
            `button_default` BOOLEAN DEFAULT TRUE,
            `created_at` DATETIME DEFAULT NOW(),
            PRIMARY KEY (`id`)
        ) $charset_collate"];
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
