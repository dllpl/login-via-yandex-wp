<?php
/**
 * @since             1.0.0
 * @package           Login via Yandex
 *
 * @wordpress-plugin
 * Plugin Name:       Login via Yandex - авторизация через Яндекс для вашего сайта или интернет магазина.
 * Plugin URI:        https://webseed.ru/blog/wordpress-plagin-dlya-avtorizaczii-cherez-yandeks-id
 * Description:       Плагин для входа через Яндекс для WordPress и Woocommerce. Укажите Client Token и Secret Token в настройках плагина, а также, выберите тип отображения на сайте (в контейнере или всплывающем окне, или и то и другое).
 * Version:           1.0.0
 * Author:            Nick Iv (веб-разработчик webseed.ru)
 * Author URI:        https://github.com/dllpl
 * License:           GPLv2
 * License URI:       https://www.gnu.org/licenses/old-licenses/gpl-2.0.html#SEC1
 */

if (!defined('ABSPATH')) exit;

if (!defined('WPINC')) {
    die;
}

add_action('rest_api_init', 'lvyid_register_routes');

add_action('wp_head', 'lvyid_add_script_to_head');
add_action('wp_footer', 'lvyid_add_script_to_footer');

add_action('admin_menu', 'lvyid_admin_menu_init');

add_filter('plugin_action_links', 'lvyid_plugin_action_links', 10, 2);

register_activation_hook(__FILE__, 'lvyid_activate');
register_uninstall_hook(__FILE__, 'lvyid_uninstall');

/** Регистрация REST API методов плагина */
function lvyid_register_routes()
{
    require_once plugin_dir_path(__FILE__) . 'app/Controllers/LVYID_MainRequestController.php';
    $controller = new LVYID_MainRequestController();
    $controller->registerRoutes();
}

function lvyid_plugin_action_links( $actions, $plugin_file ){

    if( false === strpos( $plugin_file, basename(__FILE__) ) ){
        return $actions;
    }

    $settings_link = '<a href="admin.php?page=login-via-yandex">Настройки</a>';
    array_unshift( $actions, $settings_link );

    return $actions;
}

function lvyid_admin_menu_init()
{
    require_once plugin_dir_path(__FILE__) . 'admin/LVYID_AdminController.php';
    $option = new LVYID_AdminController();
    $option->addMenu();
}

function lvyid_add_script_to_head()
{

    if (!is_user_logged_in()) {
        wp_enqueue_script('sdk-suggest-with-polyfills-latest', 'https://yastatic.net/s3/passport-sdk/autofill/v1/sdk-suggest-with-polyfills-latest.js', [], '1.0.0', 'in_footer');
    }

}

function lvyid_add_script_to_footer()
{

    if (!is_user_logged_in()) {
        require_once plugin_dir_path(__FILE__) . 'app/Controllers/LVYID_PublicController.php';

        $public = new LVYID_PublicController();
        $public->scriptInit();
    }
}


function lvyid_activate()
{
    require_once plugin_dir_path(__FILE__) . 'includes/LVYID_Activator.php';
    LVYID_Activator::make();
}

function lvyid_uninstall()
{
    require_once plugin_dir_path(__FILE__) . 'includes/LVYID_Uninstall.php';
    LVYID_Uninstall::make();
}
