<?php
if (!defined('ABSPATH')) exit;

trait LVYID_Options
{
    public static function getOptions()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'login_via_yandex_options';

        $row = $wpdb->get_row("SELECT * FROM {$table_name} ORDER BY id DESC LIMIT 1");

        if ($row) {
            return [
                'client_id'            => $row->client_id ?? '',
                'client_secret'        => $row->client_secret ?? '',
                'button'               => (bool)($row->button ?? false),
                'container_id'         => $row->container_id ?? '',
                'widget'               => (bool)($row->widget ?? false),
                'alternative'          => (bool)($row->alternative ?? false),
                'button_default'       => (bool)($row->button_default ?? false),
                'copyright'            => isset($row->copyright) ? (bool)$row->copyright : true,
                'use_ajax_webhook'     => !empty($row->use_ajax_webhook),
                'button_view'          => !empty($row->button_view) ? $row->button_view : 'main',
                'button_theme'         => !empty($row->button_theme) ? $row->button_theme : 'light',
                'button_size'          => !empty($row->button_size) ? $row->button_size : 'm',
                'button_border_radius' => isset($row->button_border_radius) && $row->button_border_radius !== '' ? (string)$row->button_border_radius : '8',
                'button_icon'          => !empty($row->button_icon) ? $row->button_icon : 'ya',
            ];
        } else {
            return [
                'client_id'            => '',
                'client_secret'        => '',
                'button'               => false,
                'container_id'         => '',
                'widget'               => false,
                'alternative'          => false,
                'button_default'       => false,
                'copyright'            => true,
                'use_ajax_webhook'     => true,
                'button_view'          => 'main',
                'button_theme'         => 'light',
                'button_size'          => 'm',
                'button_border_radius' => '8',
                'button_icon'          => 'ya',
            ];
        }
    }

    public function setOptions()
    {

    }
}
