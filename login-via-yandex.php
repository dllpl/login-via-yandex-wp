<?php
/**
 * @since             1.0.6
 * @package           Login via Yandex
 *
 * @wordpress-plugin
 * Plugin Name:       Login via Yandex - авторизация через Яндекс для вашего сайта или интернет магазина.
 * Plugin URI:        https://webseed.ru/blog/wordpress-plagin-dlya-avtorizaczii-cherez-yandeks-id
 * Description:       Плагин для входа через Яндекс для WordPress и Woocommerce. Укажите Client Token и Secret Token в настройках плагина, а также, выберите тип отображения на сайте (в контейнере или всплывающем окне, или и то и другое).
 * Version:           1.0.6
 * Author:            Никита Ив (веб-разработчик webseed.ru)
 * Author URI:        https://webseed.ru
 * License:           GPLv2
 * License URI:       https://www.gnu.org/licenses/old-licenses/gpl-2.0.html#SEC1
 */

if (!defined('ABSPATH')) exit;

if (!defined('WPINC')) {
    die;
}

add_action('rest_api_init', 'lvyid_register_routes');
add_action('wp_head', 'lvyid_add_script_to_head');
add_action('wp_footer', 'lvyid_init_script_and_style');

add_action('login_head', 'lvyid_add_script_to_head');
add_action('login_footer', 'lvyid_init_script_and_style');

add_action('admin_menu', 'lvyid_admin_menu_init');
add_action('upgrader_process_complete', 'lvyid_upgrade_function', 10, 2);
add_action('wp_footer', 'lvyid_add_copyright');
add_action('admin_init', 'lvyid_redirect_after_activation');

add_filter('plugin_action_links', 'lvyid_plugin_action_links', 10, 2);
add_filter('rest_authentication_errors', 'lvyid_rest_api_wp', 999);

register_activation_hook(__FILE__, 'lvyid_activate');
register_uninstall_hook(__FILE__, 'lvyid_uninstall');

add_action('login_form', 'lvyid_add_default_auth_button');
add_action('register_form', 'lvyid_add_default_auth_button');

add_action('woocommerce_register_form_end', 'lvyid_add_default_auth_button');
add_action('woocommerce_login_form_end', 'lvyid_add_default_auth_button');

add_filter('clearfy_rest_api_white_list', function ($white_list) {
    $white_list[] = 'login_via_yandex';
    return $white_list;
});

function lvyid_add_default_auth_button()
{
    require_once plugin_dir_path(__FILE__) . 'app/Controllers/LVYID_PublicController.php';
    $public = new LVYID_PublicController();
    $public->defaultAuthButtonsInit();
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

function lvyid_upgrade_function($upgrader_object, $options)
{
    require_once plugin_dir_path(__FILE__) . 'includes/LVYID_Upgrade.php';

    $LVYID_Upgrade = new LVYID_Upgrade();
    $LVYID_Upgrade->make($upgrader_object, $options);
}

function lvyid_add_script_to_head()
{
    if (!is_user_logged_in()) {
        wp_enqueue_script('sdk-suggest-with-polyfills-latest', 'https://yastatic.net/s3/passport-sdk/autofill/v1/sdk-suggest-with-polyfills-latest.js', [], '1.0.6', 'in_footer');
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
        $hostname = $_SERVER['HTTP_HOST'];
        echo '<a title="Заказать сайт. Разработка сайтов и плагинов на WordPress от Webseed.ru" class="login_via_yandex" href="' . esc_url("https://webseed.ru/?utm_source=$hostname&utm_medium=login_via_yandex&utm_campaign=login_via_yandex") . '">Заказать разработку сайта или плагина на Wordpress</a>';
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
