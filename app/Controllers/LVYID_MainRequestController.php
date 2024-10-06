<?php
if (!defined('ABSPATH')) exit;

class LVYID_MainRequestController extends WP_REST_Controller
{
    const NAMESPACE = 'login_via_yandex';

    public function registerRoutes()
    {
        register_rest_route(self::NAMESPACE, 'webhook', [
            'methods' => 'GET',
            'callback' => [$this, 'webhookHandler'],
            'permission_callback' => '__return_true',
            'args' => [
                'code' => [
                    'description' => 'Проверьте поле code',
                    'type' => 'string',
                    'minLength' => 3,
                    'required' => true,
                ],
            ]
        ]);
        register_rest_route(self::NAMESPACE, 'updateSettings', [
            'methods' => 'POST',
            'callback' => [$this, 'updateSettings'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
            'args' => [
                'client_id' => [
                    'description' => 'Проверьте поле client_id',
                    'type' => 'string',
                    'minLength' => 32,
                    'maxLength' => 32,
                    'required' => true,
                ],
                'client_secret' => [
                    'description' => 'Проверьте поле client_secret',
                    'type' => 'string',
                    'minLength' => 32,
                    'maxLength' => 32,
                    'required' => true,
                ],
                'button' => [
                    'description' => 'Проверьте поле button',
                    'type' => 'boolean',
                    'required' => false,
                ],
                'container_id' => [
                    'description' => 'Проверьте поле container_id',
                    'type' => 'string',
                    'minLength' => 3,
                    'maxLength' => 100,
                    'required' => false,
                ],
                'widget' => [
                    'description' => 'Проверьте поле widget',
                    'type' => 'boolean',
                    'required' => false,
                ]
            ]
        ]);
    }

    public function webhookHandler(WP_REST_Request $request)
    {
        require_once plugin_dir_path(__FILE__) . '../Service/LVYID_YandexLogin.php';

        $result = new LVYID_YandexLogin();
        $access_token = $result->getAccessToken($request['code'])['access_token'];

        if (!$access_token) {
            return wp_send_json_error($access_token);
        } else if (isset($access_token['error'])) {
            return wp_send_json_error($access_token);
        }

        require_once plugin_dir_path(__FILE__) . 'LVYID_UserController.php';

        $result = new LVYID_UserController();
        return $result->handler($access_token);
    }

    public function updateSettings(WP_REST_Request $request)
    {
        require_once plugin_dir_path(__FILE__) . '../../admin/LVYID_AdminController.php';
        return LVYID_AdminController::updateSettings($request);
    }

}
