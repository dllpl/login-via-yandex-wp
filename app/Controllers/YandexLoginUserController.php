<?php

require_once plugin_dir_path(__FILE__) . '../Service/YandexLogin.php';

class YandexLoginUserController
{
    public function handler($access_token)
    {
        global $wpdb;

        if (empty($access_token)) {
            return wp_send_json_error('Невозможно авторизовать пользователя.');
        }

        $yandexApi = new YandexLogin();
        $user_data = $yandexApi->getInfo(sanitize_text_field($access_token));

        $email = $user_data->default_email ?? null;

        if (is_null($email)) {
            return wp_send_json_error('Невозможно авторизовать пользователя.');
        }

        $table = $wpdb->prefix . "users";

        $user = $wpdb->get_row($wpdb->prepare("SELECT ID FROM $table WHERE user_email = %s", $email));

        if ($user) {
            wp_set_auth_cookie($user->ID);
        } else {
            $this->yandexid_create_user($user_data);
        }

        header('Content-Type: text/html');
        echo '
        <script>
            document.cookie = "yandex-id-logged=1; max-age=3; path=/;";
            close();
        </script>';
        die;
    }

    private function yandexid_create_user($user_data)
    {
        $userdata = [
            'first_name' => $user_data->first_name ?? null,
            'last_name' => $user_data->last_name ?? null,
            'display_name' => $user_data->first_name ?? null . ' ' . $user_data->last_name ?? null,
            'user_login' => $user_data->default_email,
            'user_pass' => wp_generate_password(8, false),
            'user_email' => $user_data->default_email,
            'description' => $user_data->default_phone->number ?? null . ' ' . $user_data->birthday ?? null
        ];

        $user_id = wp_insert_user($userdata);

        if (!is_wp_error($user_id)) {
            wp_set_auth_cookie($user_id);
            wp_send_new_user_notifications($user_id);
            return true;
        } else {
            $user_id->get_error_message();
            return false;
        }
    }
}
