<?php

class MainRequestController extends WP_REST_Controller
{
    const NAMESPACE = 'yandexid_webseed';

    public function registerRoutes()
    {
        register_rest_route(self::NAMESPACE, 'webhook', [
            'methods' => 'GET',
            'callback' => [$this, 'webhookHandler'],
            'permission_callback' => '__return_true',
            'args' => [
                'code' => [
                    'description' => __('code field is missing'),
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
                    'description' => __('Проверьте поле client_id'),
                    'type' => 'string',
                    'minLength' => 32,
                    'maxLength' => 32,
                    'required' => true,
                ],
                'client_secret' => [
                    'description' => __('Проверьте поле client_secret'),
                    'type' => 'string',
                    'minLength' => 32,
                    'maxLength' => 32,
                    'required' => true,
                ],
                'button' => [
                    'description' => __('Проверьте поле button'),
                    'type' => 'boolean',
                    'required' => false,
                ],
                'container_id' => [
                    'description' => __('Проверьте поле container_id'),
                    'type' => 'string',
                    'minLength' => 3,
                    'maxLength' => 100,
                    'required' => false,
                ],
                'widget' => [
                    'description' => __('Проверьте поле widget'),
                    'type' => 'boolean',
                    'required' => false,
                ]
            ]
        ]);
    }

    public function webhookHandler(WP_REST_Request $request)
    {
        require_once plugin_dir_path(__FILE__) . '../Service/Yandex.php';

        $result = new Yandex();
        $access_token = $result->getAccessToken($request['code'])['access_token'];

        if(!$access_token) {

            return wp_send_json_error('Не указан client secret');
        } else if (isset($access_token['error'])) {
            return wp_send_json_error($access_token);
        }

        require_once plugin_dir_path(__FILE__) . 'UserController.php';

        $result = new UserController();
        return $result->handler($access_token);
    }

    public function updateSettings(WP_REST_Request $request)
    {
        require_once plugin_dir_path(__FILE__) . '../../admin/AdminController.php';
        return AdminController::updateSettings($request);
    }

}
