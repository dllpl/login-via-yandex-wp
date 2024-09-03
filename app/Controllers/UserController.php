<?php

require_once plugin_dir_path(__FILE__) . '../Service/YandexLogin.php';

class UserController
{
    public function handler($access_token)
    {
        if (empty($access_token)) {
            return wp_send_json_error('Невозможно авторизовать пользователя.');
        }

        $yandexApi = new YandexLogin();
        $user_data = $yandexApi->getInfo(sanitize_text_field($access_token));

        $email = $user_data->default_email ?? null;

        if (is_null($email)) {
            return wp_send_json_error('Невозможно авторизовать пользователя.');
        }

        $user = get_user_by('email', $email);

        if ($user) {
            wp_set_auth_cookie($user->ID);
        } else {
            $this->yandexid_create_user($user_data);
        }
        header('Content-Type: text/html; charset=UTF-8');
        echo "<script>window.opener.parent.location.reload();window.close();</script>";
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
            'meta_input' => [
                'yandex_phone' => $user_data->default_phone->number ?? null,
                'yandex_birthday' => $user_data->birthday ?? null,
                'yandex_gender' => $user_data->sex ?? null,
                'yandex_login' => $user_data->login ?? null,
                'yandex_id' => $user_data->id ?? null,
                'yandex_real_name' => $user_data->real_name ?? null,
                'yandex_display_name' => $user_data->display_name ?? null,
            ]
        ];

        if(!empty($user_data->is_avatar_empty)) {
            $userdata['meta_input']['yandex_avatar'] = "https://avatars.yandex.net/get-yapic/{$user_data->default_avatar_id}/islands-200";
        }

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
