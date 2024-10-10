<?php

defined('PLUGINPATH') or exit('No direct script access allowed');

/*
  Plugin Name: Telegram Notification
  Description: Telegram Notification for RISE CRM.
  Version: 1.0
  Requires at least: 3.3
  Author: ClassicCompiler
  Author URL: https://codecanyon.net/user/classiccompiler/portfolio
 */

//add admin setting menu item
app_hooks()->add_filter('app_filter_admin_settings_menu', function ($settings_menu) {
    $settings_menu["plugins"][] = array("name" => "telegram_notification", "url" => "telegram_notification_settings");
    return $settings_menu;
});

//install dependencies
register_installation_hook("Telegram_Notification", function ($item_purchase_code) {
    include PLUGINPATH . "Telegram_Notification/install/do_install.php";
});

//add setting link to the plugin setting
app_hooks()->add_filter('app_filter_action_links_of_Telegram_Notification', function ($action_links_array) {
    $action_links_array = array(
        anchor(get_uri("telegram_notification_settings"), app_lang("settings"))
    );

    return $action_links_array;
});

//update plugin
use Telegram_Notification\Controllers\Telegram_Notification_Updates;

register_update_hook("Telegram_Notification", function () {
    $update = new Telegram_Notification_Updates();
    return $update->index();
});

//uninstallation: remove data from database
register_uninstallation_hook("Telegram_Notification", function () {
    $dbprefix = get_db_prefix();
    $db = db_connect('default');

    $sql_query = "DROP TABLE IF EXISTS `" . $dbprefix . "telegram_integration_settings`;";
    $db->query($sql_query);

    $sql_query = "DROP TABLE IF EXISTS `" . $dbprefix . "telegram_notification_settings`;";
    $db->query($sql_query);
});


app_hooks()->add_action('app_hook_post_notification', function ($notification_id) {
    $Telegram_Notifications_model = new \Telegram_Notification\Models\Telegram_Notifications_model();

    if (get_telegram_notification_setting("enable_telegram") && get_telegram_notification_setting("bot_token") && get_telegram_notification_setting("chat_id")) {
        $Telegram_Notifications_model->create_notification($notification_id);
    }
});