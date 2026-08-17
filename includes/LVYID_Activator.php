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
            `button_default` BOOLEAN DEFAULT FALSE,
            `copyright` BOOLEAN DEFAULT TRUE,
            `use_ajax_webhook` BOOLEAN DEFAULT FALSE,
            `button_view` VARCHAR(32) DEFAULT 'main',
            `button_theme` VARCHAR(32) DEFAULT 'light',
            `button_size` VARCHAR(16) DEFAULT 'm',
            `button_border_radius` VARCHAR(16) DEFAULT '8',
            `button_icon` VARCHAR(32) DEFAULT 'ya',
            `created_at` DATETIME DEFAULT NOW(),
            PRIMARY KEY (`id`)
        ) $charset_collate"];
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
