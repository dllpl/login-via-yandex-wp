<?php
if ( ! defined( 'ABSPATH' ) ) exit;
trait LVYID_Options
{
    public static function getOptions()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'login_via_yandex_options';

        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} ORDER BY id DESC LIMIT 1"));

        if ($row) {
            return [
                'client_id' => $row->client_id,
                'client_secret' => $row->client_secret,
                'button' => (bool) $row->button,
                'container_id' => $row->container_id,
                'widget' => (bool) $row->widget,
            ];
        } else {
            return false;
        }
    }

    public function setOptions()
    {

    }
}
