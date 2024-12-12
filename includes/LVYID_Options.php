<?php
if (!defined('ABSPATH')) exit;

trait LVYID_Options
{
    public static function getOptions()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'login_via_yandex_options';

        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} ORDER BY id DESC LIMIT 1"));

        if ($row) {
            return [
                'client_id' => $row->client_id ?? null,
                'client_secret' => $row->client_secret ?? null,
                'button' => (bool)$row->button ?? false,
                'container_id' => $row->container_id ?? null,
                'widget' => (bool)$row->widget ?? false,
                'alternative' => (bool)$row->alternative ?? false,
                'button_default' => (bool)$row->button_default ?? false,
            ];
        } else {
            return false;
        }
    }

    public function setOptions()
    {

    }
}
