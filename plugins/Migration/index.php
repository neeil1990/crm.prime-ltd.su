<?php

//Prevent direct access
defined('PLUGINPATH') or exit('No direct script access allowed');

/*
Plugin Name: Migration
Description: Migrate tables to data base
Version: 1.0
Requires at least: 2.8
Author: PRIME
*/

app_hooks()->add_filter('app_filter_action_links_of_Migration', function ($action_links_array) {
    $action_links_array = array(
        anchor("migration", "Обновить БД")
    );

    return $action_links_array;
});

//installation: install dependencies
register_installation_hook("Migration", function ($item_purchase_code) {
    include PLUGINPATH . "Migration/install/do_install.php";
});

//uninstallation: remove data from database
register_uninstallation_hook("Migration", function () {
    $dbprefix = get_db_prefix();
    $db = db_connect('default');

    $sql_query = "DROP TABLE `" . $dbprefix . "pin_ticket_comments`;";
    $db->query($sql_query);
});
