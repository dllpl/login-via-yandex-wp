<?php
/**
 * @since             1.0.0
 * @package           Authorization via Yandex ID
 *
 * @wordpress-plugin
 * Plugin Name:       Yandex Login - авторизация через Яндекс для WordPress и Woocommerce.
 * Plugin URI:        https://webseed.ru/blog/wordpress-plagin-dlya-avtorizaczii-cherez-yandeks-id
 * Description:       Плагин для входа через Яндекс для WordPress и Woocommerce. Укажите Client Token и Secret Token в настройках плагина, а также, выберите тип отображения на сайте (в контейнере или всплывающем окне, или и то и другое).
 * Version:           1.0.0
 * Author:            Nikita Ivanov (Nick Iv)
 * Author URI:        https://github.com/dllpl
 * License:           GPLv2
 * License URI:       https://www.gnu.org/licenses/old-licenses/gpl-2.0.html#SEC1
 */

if (!defined('WPINC')) {
    die;
}

add_action('rest_api_init', 'register_routes');

add_action( 'wp_head', 'add_script_to_head' );
add_action( 'wp_footer', 'add_script_to_footer' );

add_action('admin_menu', 'admin_menu_init');

register_activation_hook(__FILE__, 'activate');
register_uninstall_hook(__FILE__, 'uninstall');

/** Регистрация REST API методов плагина */
function register_routes()
{
    require_once plugin_dir_path(__FILE__) . 'app/Controllers/YandexLoginMainRequestController.php';
    $controller = new YandexLoginMainRequestController();
    $controller->registerRoutes();
}

function admin_menu_init()
{
    require_once plugin_dir_path(__FILE__) . 'admin/YandexLoginAdminController.php';
    $option = new YandexLoginAdminController();
    $option->addMenu();
}
function add_script_to_head() {

    if (!is_user_logged_in()) {
        wp_enqueue_script( 'sdk-suggest-with-polyfills-latest', 'https://yastatic.net/s3/passport-sdk/autofill/v1/sdk-suggest-with-polyfills-latest.js', [], '');
    }

}

function add_script_to_footer() {

    if (!is_user_logged_in()) {
        require_once plugin_dir_path(__FILE__) . 'app/Controllers/YandexLoginPublicController.php';

        $public = new YandexLoginPublicController();
        $public->scriptInit();
    }
}


function activate()
{
    require_once plugin_dir_path(__FILE__) . 'includes/YandexLoginActivator.php';
    YandexLoginActivator::make();
}

function uninstall()
{
    require_once plugin_dir_path(__FILE__) . 'includes/YandexLoginUninstall.php';
    YandexLoginUninstall::make();
}
