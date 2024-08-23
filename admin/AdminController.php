<?php

require_once plugin_dir_path(__FILE__) . '../includes/Options.php';

class AdminController
{
    use Options;

    private $options;

    public function __construct()
    {
        $options = Options::getOptions();
        $this->options = $options ?? null;
    }

    public function addMenu()
    {
        add_options_page('Вход через Яндекс', 'Вход через Яндекс от Webseed', 'manage_options', 'login_via_yandex', [$this, 'settingsPage']);
    }

    public function settingsPage()
    {
        $options = $this->options;

        ?>
        <style>
            label {
                font-size: 16px
            }
        </style>
        <div class="wrap">
            <h2><?php echo sprintf('%s', esc_html(get_admin_page_title())) ?></h2>
            <form>
                <h3>Данные приложения Яндекс ID и настройки плагина</h3>
                <label for="client_id">ClientID<span style="color: red">*</span></label> <br>
                <input type="text" id="client_id" name="client_id" value="<?php echo sprintf('%s', esc_attr($options['client_id'] ?? '')) ?>"
                       required maxlength="32" minlength="32"><br>
                <label for="client_secret">Client secret<span style="color: red">*</span></label> <br>
                <input type="password" id="client_secret" name="client_secret"
                       value="<?php echo sprintf('%s', esc_attr($options['client_secret'] ?? '')) ?>"
                       required maxlength="32" minlength="32"><br>
                <h3>Виджет и кнопка <small>(возможны оба варианта, либо только один, либо вообще ни один)</small></h3>
                <div style="display: flex; margin-bottom: 15px">
                    <div style="display: flex; align-items: end">
                        <label for="widget">Показать виджет</label>
                        <input type="checkbox" id="widget"
                               name="widget" <?php if (isset($options['widget']) && $options['widget']) echo 'checked' ?>
                               style="margin-left: 5px">
                    </div>
                    <div style="display: flex; align-items: end; margin-left: 20px">
                        <label for="button">Показать кнопку</label>
                        <input type="checkbox" id="button"
                               name="button" <?php if (isset($options['button']) && $options['button']) echo 'checked' ?>
                               style="margin-left: 5px">
                    </div>
                </div>
                <div style="display: flex; flex-direction: column">
                    <label for="container_id">ID - контейнера кнопки</label>
                    <input type="text" id="container_id" name="container_id"
                           value="<?php echo sprintf('%s', esc_attr($options['container_id'] ?? '')) ?>"
                        <?php if (!isset($options['button']) || !$options['button']) echo 'disabled'?>
                        placeholder='Поле активируется, если выбрать "Показать кнопку"'
                    >
                </div>

                <div style="color: red" id="error"></div>
                <div style="color: forestgreen" id="success"></div>
                <?php
                submit_button();
                ?>
            </form>
            <script>
                document.getElementById('button').onchange = (event) => {
                    if (event.target.checked) {
                        document.getElementById('container_id').removeAttribute('disabled')
                    } else {
                        document.getElementById('container_id').setAttribute("disabled", "disabled");
                    }
                }

                const form = document.querySelector('form');
                const url = '/wp-json/login_via_yandex/updateSettings';

                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    const formData = new FormData(form);
                    const data = {};

                    for (let [key, value] of formData.entries()) {
                        if (value === '') continue

                        if(key === 'button' || key === 'widget') {
                            value = value === 'on';
                        }

                        data[key] = value;
                    }
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-WP-Nonce': "<?php echo sprintf('%s', esc_html(wp_create_nonce('wp_rest'))) ?>",
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                document.getElementById('error').innerText = data?.message ?? data.data
                            } else {
                                document.getElementById('success').innerText = data.data
                            }
                        })
                        .catch(error => {
                            document.getElementById('error').innerText = error.data.message
                        })
                        .finally(() => {
                            setTimeout(() => {
                                document.getElementById('error').innerText = ''
                                document.getElementById('success').innerText = ''
                            }, 5000)
                        })
                });
            </script>
            <style>
                input {
                    width: 400px;
                }
            </style>
        </div>
        <?php


    }

    public static function updateSettings(WP_REST_Request $request)
    {
        if(!empty($request['button']) && empty($request['container_id'])) {
            return wp_send_json_error('Заполните поле "ID - контейнера кнопки"');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'login_via_yandex_options';

        $data = [
            'client_id' => isset($request['client_id']) ? trim(sanitize_text_field($request['client_id'])) : '',
            'client_secret' => isset($request['client_secret']) ? trim(sanitize_text_field($request['client_secret'])) : '',
            'button' => $request['button'] ?? null,
            'container_id' => isset($request['container_id']) ? sanitize_text_field($request['container_id']) : null,
            'widget' => $request['widget'] ?? null,
        ];

        $result = $wpdb->insert($table_name, $data);

        if ($result) {
            return wp_send_json_success('Успешное сохранение данных');
        } else {
            return wp_send_json_error('Ошибка при сохранении данных');
        }
    }
}
