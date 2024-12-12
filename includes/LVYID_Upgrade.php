<?php
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . '../app/LVYID_Logger.php';

class LVYID_Upgrade
{
    private $log_class;

    public function __construct()
    {
        $this->log_class = new LVYID_Logger();
    }

    public function make($upgrader_object, $options)
    {
        if ($options['action'] === 'update' && $options['type'] === 'plugin' && isset($options['plugins'])) {
            $current_plugin = plugin_basename(__FILE__);

            if (in_array($current_plugin, $options['plugins'])) {

                if (file_exists(plugin_dir_path(__FILE__) . '../update/plugin_data.json')) {

                    $plugin_data = json_decode(file_get_contents(plugin_dir_path(__FILE__) . '../update/plugin_data.json'), true);

                    if ($plugin_data && isset($plugin_data['version'])) {
                        $this->log_class->info('Текущая параметры плагина: ' . json_encode($plugin_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    } else {
                        $this->log_class->error('Файл plugin_data.json содержит некорректные данные');
                    }

                } else {
                    $this->log_class->info('Файл plugin_data.json не найден, создаем');

                    file_put_contents(plugin_dir_path(__FILE__) . '../update/plugin_data.json', '{}');
                }

                $this->log_class->log('Обновление плагина');

                $plugin_data = get_plugin_data(__FILE__);

                $new_version = $plugin_data['Version'];

                $this->log_class->log('Новая версия плагина: ' . $new_version);

                $file_path = plugin_dir_path(__FILE__) . '../update/plugin_data.json';

                $data = [
                    'version' => $new_version,
                    'updated_at' => date('d.m.Y H:i:s'),
                ];

                file_put_contents($file_path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                $this->log_class->info('Файл plugin_data.json обновлен');

                $this->log_class->info('Запуск действий по обновлению');

                if ($new_version === '1.0.5') {
                    $this->log_class->info('Работаем с добавлением столбца `alternative`');
                    $this->add_alternative_column();
                }

                if ($new_version === '1.0.6') {
                    $this->log_class->info('Работаем с добавлением столбца `button_default`');
                    $this->add_button_default_column();
                }
            }
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
             ADD COLUMN `button_default` BOOLEAN DEFAULT TRUE;"
            );
            $this->log_class->info("Столбец `button_default` был успешно добавлен в таблицу опций плагина");
        } else {
            $this->log_class->info("Столбец `button_default` уже существует в таблице опций плагина");
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
        } else {
            $this->log_class->info("Столбец `alternative` уже существует в таблице опций плагина");
        }
        return true;
    }
}
