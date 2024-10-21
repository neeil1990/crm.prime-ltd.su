<?php

namespace Config;

$routes = Services::routes();

$password_manager_namespace = ['namespace' => 'Migration\Controllers'];

$routes->get('migration', 'Migration::index', $password_manager_namespace);
