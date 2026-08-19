<?php
/**
 * @since             2.0.0
 * @package           Login via Yandex
 *
 * @wordpress-plugin
 * Plugin Name:       Login via Yandex - авторизация через Яндекс для вашего сайта или интернет магазина.
 * Plugin URI:        https://webseed.ru
 * Description:       Плагин для входа через Яндекс для WordPress и Woocommerce. Укажите Client Token и Secret Token в настройках плагина, а также, выберите тип отображения на сайте (в контейнере или всплывающем окне, или и то и другое).
 * Version:           2.0.0
 * Author:            Никита Ив (веб-разработчик webseed.ru)
 * Author URI:        https://webseed.ru
 * License:           GPLv2
 * License URI:       https://www.gnu.org/licenses/old-licenses/gpl-2.0.html#SEC1
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Text Domain:       login-via-yandex
 */

if (!defined('ABSPATH')) exit;

if (!defined('WPINC')) {
    die;
}

if (!defined('LVYID_VERSION')) {
    define('LVYID_VERSION', '2.0.0');
}

if (!defined('LVYID_PLUGIN_FILE')) {
    define('LVYID_PLUGIN_FILE', __FILE__);
}

add_action('rest_api_init', 'lvyid_register_routes');
add_action('wp_head', 'lvyid_add_script_to_head');
add_action('wp_footer', 'lvyid_init_script_and_style');

add_action('login_head', 'lvyid_add_script_to_head');
add_action('login_footer', 'lvyid_init_script_and_style');

add_action('admin_menu', 'lvyid_admin_menu_init');
add_action('admin_init', 'lvyid_check_for_upgrade');
add_action('admin_init', 'lvyid_redirect_after_activation');
add_action('upgrader_process_complete', 'lvyid_upgrade_function', 10, 2);
add_action('wp_footer', 'lvyid_add_copyright');

add_filter('plugin_action_links', 'lvyid_plugin_action_links', 10, 2);
add_filter('rest_authentication_errors', 'lvyid_rest_api_wp', 999);

register_activation_hook(__FILE__, 'lvyid_activate');
register_uninstall_hook(__FILE__, 'lvyid_uninstall');

add_action('login_form', 'lvyid_add_default_auth_button');
add_action('register_form', 'lvyid_add_default_auth_button');

add_action('woocommerce_register_form_end', 'lvyid_add_default_auth_button');
add_action('woocommerce_login_form_end', 'lvyid_add_default_auth_button');

/** Автоматическое заполнение телефона в чекауте WooCommerce из данных Яндекс ID */
add_filter('woocommerce_checkout_get_value', 'lvyid_woocommerce_checkout_get_value', 10, 2);

function lvyid_woocommerce_checkout_get_value($value, $input)
{
    if (empty($value) && is_user_logged_in()) {
        $user_id = get_current_user_id();
        if ($input === 'billing_phone') {
            $phone = get_user_meta($user_id, 'billing_phone', true);
            if (empty($phone)) {
                $phone = get_user_meta($user_id, 'yandex_phone', true);
            }
            return !empty($phone) ? $phone : $value;
        }
    }
    return $value;
}

/** AJAX-авторизация (работает даже при заблокированном WP JSON API) */
add_action('wp_ajax_nopriv_lvyid_auth_user', 'lvyid_ajax_auth_user');
add_action('wp_ajax_lvyid_auth_user', 'lvyid_ajax_auth_user');

add_action('wp_ajax_nopriv_lvyid_webhook', 'lvyid_ajax_webhook');
add_action('wp_ajax_lvyid_webhook', 'lvyid_ajax_webhook');

add_action('wp_ajax_lvyid_update_settings', 'lvyid_ajax_update_settings');
add_action('wp_ajax_lvyid_dismiss_welcome', 'lvyid_ajax_dismiss_welcome');

add_filter('clearfy_rest_api_white_list', function ($white_list) {
    $white_list[] = 'login_via_yandex';
    return $white_list;
});

function lvyid_ajax_auth_user()
{
    check_ajax_referer('lvyid_auth_nonce', 'nonce');

    $access_token = isset($_POST['access_token']) ? sanitize_text_field(wp_unslash($_POST['access_token'])) : '';

    if (empty($access_token)) {
        wp_send_json_error('Не передан access_token');
    }

    require_once plugin_dir_path(__FILE__) . 'app/Controllers/LVYID_UserController.php';
    $result = new LVYID_UserController();
    $result->handler($access_token);
}

function lvyid_ajax_webhook()
{
    require_once plugin_dir_path(__FILE__) . 'app/Controllers/LVYID_MainRequestController.php';
    $controller = new LVYID_MainRequestController();
    $controller->handleWebhook();
}

function lvyid_ajax_update_settings()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Доступ запрещен');
    }

    check_ajax_referer('lvyid_admin_nonce', 'nonce');

    require_once plugin_dir_path(__FILE__) . 'admin/LVYID_AdminController.php';
    return LVYID_AdminController::ajaxUpdateSettings($_POST);
}

function lvyid_ajax_dismiss_welcome()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Доступ запрещен');
    }

    require_once plugin_dir_path(__FILE__) . 'admin/LVYID_AdminController.php';
    LVYID_AdminController::ajaxDismissWelcome();
}

function lvyid_add_default_auth_button()
{
    require_once plugin_dir_path(__FILE__) . 'app/Controllers/LVYID_PublicController.php';
    $public = new LVYID_PublicController();
    $public->defaultAuthButtonsInit();
}

/** Регистрация шорткодов [login_via_yandex] и [yandex_login] */
add_shortcode('login_via_yandex', 'lvyid_shortcode_auth_button');
add_shortcode('yandex_login', 'lvyid_shortcode_auth_button');

function lvyid_shortcode_auth_button($atts = [])
{
    require_once plugin_dir_path(__FILE__) . 'app/Controllers/LVYID_PublicController.php';
    return LVYID_PublicController::shortcodeButton($atts);
}

function lvyid_rest_api_wp($result)
{
    if (!empty($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/wp-json/login_via_yandex/') !== false) {
        return null;
    }
    return $result;
}

/** Регистрация REST API методов плагина */
function lvyid_register_routes()
{
    require_once plugin_dir_path(__FILE__) . 'app/Controllers/LVYID_MainRequestController.php';
    $controller = new LVYID_MainRequestController();
    $controller->registerRoutes();
}

function lvyid_plugin_action_links($actions, $plugin_file)
{
    if (false === strpos($plugin_file, basename(__FILE__))) {
        return $actions;
    }

    $settings_link = '<a href="admin.php?page=login_via_yandex">Настройки</a>';
    array_unshift($actions, $settings_link);

    return $actions;
}

function lvyid_admin_menu_init()
{
    require_once plugin_dir_path(__FILE__) . 'admin/LVYID_AdminController.php';
    $option = new LVYID_AdminController();
    $option->addMenu();
}

function lvyid_check_for_upgrade()
{
    if (is_admin()) {
        require_once plugin_dir_path(__FILE__) . 'includes/LVYID_Upgrade.php';
        $upgrade = new LVYID_Upgrade();
        $upgrade->check_and_run_upgrades();
    }
}

function lvyid_upgrade_function($upgrader_object, $options)
{
    require_once plugin_dir_path(__FILE__) . 'includes/LVYID_Upgrade.php';

    $LVYID_Upgrade = new LVYID_Upgrade();
    $LVYID_Upgrade->make($upgrader_object, $options);
}

function lvyid_add_script_to_head()
{
    if (!is_user_logged_in()) {
        wp_enqueue_script('sdk-suggest-with-polyfills-latest', 'https://yastatic.net/s3/passport-sdk/autofill/v1/sdk-suggest-with-polyfills-latest.js', [], '2.0.0', false);
    }
}

function lvyid_init_script_and_style()
{
    require_once plugin_dir_path(__FILE__) . 'app/Controllers/LVYID_PublicController.php';
    $public = new LVYID_PublicController();

    if (!is_user_logged_in()) {
        $public->scriptInit();
    }

    $public->styleInit();
}

function lvyid_add_copyright()
{
    if (!is_user_logged_in()) {
        require_once plugin_dir_path(__FILE__) . 'includes/LVYID_Options.php';
        $options = LVYID_Options::getOptions();
        $show_copyright = isset($options['copyright']) ? (bool)$options['copyright'] : true;

        if ($show_copyright) {
            $hostname = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';
            $locale   = function_exists('determine_locale') ? determine_locale() : get_locale();
            $is_ru    = (stripos($locale, 'ru') === 0);

            $title = $is_ru
                ? 'Вход через Яндекс ID разработан webseed.ru'
                : 'Login with Yandex ID developed by webseed.ru';

            $text = $is_ru
                ? 'Вход через Яндекс ID — webseed.ru'
                : 'Login with Yandex ID — webseed.ru';

            $info = $is_ru
                ? 'Копирайт можно убрать в настройках плагина'
                : 'Copyright can be removed in the plugin settings';

            echo '<a data-info="' . esc_attr($info) . '" title="' . esc_attr($title) . '" class="login_via_yandex" href="' . esc_url("https://webseed.ru/?utm_source=$hostname&utm_medium=login_via_yandex&utm_campaign=login_via_yandex") . '" target="_blank" rel="noopener">' . esc_html($text) . '</a>';
        }
    }
}


function lvyid_activate()
{
    require_once plugin_dir_path(__FILE__) . 'includes/LVYID_Activator.php';
    LVYID_Activator::make();

    add_option('lviyid_redirect_on_activation', true);
}

function lvyid_redirect_after_activation()
{
    if (get_option('lviyid_redirect_on_activation', false)) {
        delete_option('lviyid_redirect_on_activation');

        if (is_admin() && current_user_can('manage_options')) {
            wp_safe_redirect('admin.php?page=login_via_yandex');
            exit;
        }
    }
}

function lvyid_uninstall()
{
    require_once plugin_dir_path(__FILE__) . 'includes/LVYID_Uninstall.php';
    LVYID_Uninstall::make();
}
