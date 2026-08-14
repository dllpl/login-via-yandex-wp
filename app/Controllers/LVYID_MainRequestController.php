<?php
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . '../../includes/LVYID_Options.php';

class LVYID_MainRequestController extends WP_REST_Controller
{
    use LVYID_Options;

    const NAMESPACE = 'login_via_yandex';
    private $options;

    public function __construct()
    {
        $options = LVYID_Options::getOptions();
        $this->options = $options ?? null;
    }

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
                    'required' => false,
                ],
            ]
        ]);
        register_rest_route(self::NAMESPACE, 'authUser', [
            'methods' => 'POST',
            'callback' => [$this, 'authUser'],
            'permission_callback' => '__return_true',
            'args' => [
                'access_token' => [
                    'description' => 'Проверьте поле access_token',
                    'type' => 'string',
                    'minLength' => 3,
                    'required' => true,
                ]
            ]
        ]);
        register_rest_route(self::NAMESPACE, 'update-settings', [
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
                ],
                'alternative' => [
                    'description' => 'Проверьте поле alternative',
                    'type' => 'boolean',
                    'required' => false,
                ],
                'button_default' => [
                    'description' => 'Проверьте поле button_default',
                    'type' => 'boolean',
                    'required' => false,
                ],
                'copyright' => [
                    'description' => 'Проверьте поле copyright',
                    'type' => 'boolean',
                    'required' => false,
                ],
                'use_ajax_webhook' => [
                    'description' => 'Проверьте поле use_ajax_webhook',
                    'type' => 'boolean',
                    'required' => false,
                ]
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
                ],
                'alternative' => [
                    'description' => 'Проверьте поле alternative',
                    'type' => 'boolean',
                    'required' => false,
                ],
                'button_default' => [
                    'description' => 'Проверьте поле button_default',
                    'type' => 'boolean',
                    'required' => false,
                ],
                'copyright' => [
                    'description' => 'Проверьте поле copyright',
                    'type' => 'boolean',
                    'required' => false,
                ],
                'use_ajax_webhook' => [
                    'description' => 'Проверьте поле use_ajax_webhook',
                    'type' => 'boolean',
                    'required' => false,
                ]
            ]
        ]);
    }

    public function handleWebhook()
    {
        if ($this->options) {
            if (empty($this->options['alternative'])) {
                header('Content-Type: text/html; charset=utf-8');
                include plugin_dir_path(__FILE__) . '../../public/login_via_yandex.html';
                exit;
            }
        }

        $code = isset($_GET['code']) ? sanitize_text_field(wp_unslash($_GET['code'])) : (isset($_POST['code']) ? sanitize_text_field(wp_unslash($_POST['code'])) : '');
        if (empty($code)) {
            wp_send_json_error('Не передан код');
        }

        require_once plugin_dir_path(__FILE__) . '../Service/LVYID_YandexLogin.php';
        $result = new LVYID_YandexLogin();
        $data = $result->getAccessToken($code);

        if (empty($data['status'])) {
            wp_send_json_error($data['error'] ?? 'Ошибка получения токена');
        }

        require_once plugin_dir_path(__FILE__) . 'LVYID_UserController.php';
        $user_controller = new LVYID_UserController();
        $auth_res = $user_controller->handler($data['access_token']);

        if (!empty($auth_res['success'])) {
            wp_safe_redirect(home_url());
            exit;
        } else {
            return $auth_res;
        }
    }

    public function webhookHandler(WP_REST_Request $request)
    {
        return $this->handleWebhook();
    }

    public function updateSettings(WP_REST_Request $request)
    {
        require_once plugin_dir_path(__FILE__) . '../../admin/LVYID_AdminController.php';
        return LVYID_AdminController::updateSettings($request);
    }

    public function authUser(WP_REST_Request $request)
    {
        require_once plugin_dir_path(__FILE__) . 'LVYID_UserController.php';

        $result = new LVYID_UserController();
        return $result->handler($request['access_token']);
    }

}
