<?php
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . '../app/LVYID_Logger.php';
require_once plugin_dir_path(__FILE__) . 'LVYID_Activator.php';

class LVYID_Upgrade
{
    private $log_class;

    public function __construct()
    {
        $this->log_class = new LVYID_Logger();
    }

    /**
     * Проверка и выполнение обновлений структуры БД.
     * Безопасно для всех существующих 100+ сайтов.
     */
    public function check_and_run_upgrades()
    {
        $installed_version = get_option('lvyid_plugin_version', false);
        $current_version   = defined('LVYID_VERSION') ? LVYID_VERSION : '2.0.0';

        if ($installed_version === false) {
            // Если опция в БД еще не создана, проверяем legacy plugin_data.json
            $json_file = plugin_dir_path(__FILE__) . '../update/plugin_data.json';
            if (file_exists($json_file)) {
                $plugin_data = json_decode(@file_get_contents($json_file), true);
                if (!empty($plugin_data['version'])) {
                    $installed_version = $plugin_data['version'];
                }
            }
        }

        if ($installed_version !== $current_version) {
            $this->log_class->info("Обновление плагина с версии " . ($installed_version ?: 'legacy') . " до {$current_version}");
            $this->run_migrations($installed_version, $current_version);
            update_option('lvyid_plugin_version', $current_version);
            $this->update_legacy_json_file($current_version);
        }
    }

    /**
     * Применение всех миграций
     */
    public function run_migrations($from_version = null, $to_version = null)
    {
        // 1. Актуализируем структуру таблицы через dbDelta (безопасно, не удаляет данные)
        LVYID_Activator::make();

        // 2. Гарантируем наличие колонок для обратной совместимости
        $this->add_alternative_column();
        $this->add_button_default_column();
        $this->add_copyright_column();
        $this->add_use_ajax_webhook_column();

        $this->log_class->info("Миграции базы данных успешно завершены для версии {$to_version}");
    }

    /**
     * Обработчик стандартного хука upgrader_process_complete
     */
    public function make($upgrader_object, $options)
    {
        if (isset($options['action'], $options['type']) && $options['action'] === 'update' && $options['type'] === 'plugin' && isset($options['plugins'])) {
            $main_plugin_file = defined('LVYID_PLUGIN_FILE') ? LVYID_PLUGIN_FILE : (dirname(__DIR__) . '/login-via-yandex.php');
            $current_plugin   = plugin_basename($main_plugin_file);

            if (in_array($current_plugin, (array)$options['plugins'], true)) {
                $this->check_and_run_upgrades();
            }
        }
    }

    /**
     * Обновление файла plugin_data.json для обратной совместимости
     */
    private function update_legacy_json_file($version)
    {
        $file_path = plugin_dir_path(__FILE__) . '../update/plugin_data.json';
        $data = [
            'version'    => $version,
            'updated_at' => date('d.m.Y H:i:s'),
        ];
        if (is_writable(dirname($file_path)) || (file_exists($file_path) && is_writable($file_path))) {
            @file_put_contents($file_path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }

    public function add_button_default_column()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'login_via_yandex_options';

        $column_exists = $wpdb->get_results(
            $wpdb->prepare(
                "SHOW COLUMNS FROM `$table_name` LIKE %s",
                'button_default'
            )
        );

        if (empty($column_exists)) {
            $wpdb->query(
                "ALTER TABLE `$table_name`
             ADD COLUMN `button_default` BOOLEAN DEFAULT FALSE;"
            );
            $this->log_class->info("Столбец `button_default` был успешно добавлен в таблицу опций плагина");
        }
        return true;
    }

    public function add_alternative_column()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'login_via_yandex_options';

        $column_exists = $wpdb->get_results(
            $wpdb->prepare(
                "SHOW COLUMNS FROM `$table_name` LIKE %s",
                'alternative'
            )
        );

        if (empty($column_exists)) {
            $wpdb->query(
                "ALTER TABLE `$table_name`
             ADD COLUMN `alternative` BOOLEAN DEFAULT FALSE;"
            );
            $this->log_class->info("Столбец `alternative` был успешно добавлен в таблицу опций плагина");
        }
        return true;
    }

    public function add_copyright_column()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'login_via_yandex_options';

        $column_exists = $wpdb->get_results(
            $wpdb->prepare(
                "SHOW COLUMNS FROM `$table_name` LIKE %s",
                'copyright'
            )
        );

        if (empty($column_exists)) {
            $wpdb->query(
                "ALTER TABLE `$table_name`
             ADD COLUMN `copyright` BOOLEAN DEFAULT TRUE;"
            );
            $this->log_class->info("Столбец `copyright` был успешно добавлен в таблицу опций плагина");
        }
        return true;
    }

    public function add_use_ajax_webhook_column()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'login_via_yandex_options';

        $column_exists = $wpdb->get_results(
            $wpdb->prepare(
                "SHOW COLUMNS FROM `$table_name` LIKE %s",
                'use_ajax_webhook'
            )
        );

        if (empty($column_exists)) {
            $wpdb->query(
                "ALTER TABLE `$table_name`
             ADD COLUMN `use_ajax_webhook` BOOLEAN DEFAULT FALSE;"
            );
            $this->log_class->info("Столбец `use_ajax_webhook` был успешно добавлен в таблицу опций плагина");
        }
        return true;
    }
}

