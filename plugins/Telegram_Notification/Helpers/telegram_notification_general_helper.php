<?php

/**
 * get the defined config value by a key
 * @param string $key
 * @return config value
 */
if (!function_exists('get_telegram_notification_setting')) {

    function get_telegram_notification_setting($key = "") {
        $config = new Telegram_Notification\Config\Telegram_Notification();

        $setting_value = get_array_value($config->app_settings_array, $key);
        if ($setting_value !== NULL) {
            return $setting_value;
        } else {
            return "";
        }
    }

}

use App\Controllers\App_Controller;

if (!function_exists('send_telegram_notification')) {

    function send_telegram_notification($event, $user_id = 0, $notification_id = 0) {
        $bot_token = get_telegram_notification_setting("bot_token");
        $chat_id = get_telegram_notification_setting("chat_id");
        if (!($bot_token && $chat_id)) {
            return false;
        }

        $ci = new App_Controller();

        $message = app_lang("notification_" . $event);
        $notification_description = "";
        $url = "";

        if ($notification_id) {
            $to_user_name = $ci->Notifications_model->get_to_user_name($notification_id);
            if ($to_user_name) {
                $message = sprintf(app_lang("notification_" . $event), $to_user_name);
            }

            //get notification url
            $notification_info = $ci->Notifications_model->get_email_notification($notification_id);
            $url_attributes_array = get_notification_url_attributes($notification_info);
            $url = get_array_value($url_attributes_array, "url");

            //prepare notification details
            $notification_description = view("Telegram_Notification\Views\\notifications\\notification_description_for_telegram", array("notification" => $notification_info));
        }

        $user_info = $ci->Users_model->get_one($user_id);
        $title = $user_id ? ($user_info->first_name . " " . $user_info->last_name) : get_setting('app_title');
        $message = $url ? "<a href='$url'>$message</a>" : $message;

        $telegram_message = "<b>$title</b> $message";
        if ($notification_description) {
            $telegram_message .= $notification_description;
        }

        $data = array(
            "chat_id" => $chat_id,
            "parse_mode" => "HTML",
            "text" => $telegram_message,
        );

        $ch = curl_init("https://api.telegram.org/bot$bot_token/sendMessage");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        if (!$result) {
            return false;
        }

        try {
            $result = json_decode($result);
        } catch (\Exception $ex) {
            return false;
        }

        if ($result->ok) {
            return true;
        }

        return false;
    }

}
