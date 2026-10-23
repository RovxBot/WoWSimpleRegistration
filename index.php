<?php
/**
 * @author Amin Mahmoudi (MasterkinG)
 * @copyright    Copyright (c) 2019 - 2024, MasterkinG32 (https://masterking32.com)
 * @link    https://masterking32.com
 **/

if (!((PHP_MAJOR_VERSION === 8 && PHP_MINOR_VERSION === 4 && version_compare(PHP_VERSION, '8.4.27', '>='))
    || (PHP_MAJOR_VERSION === 8 && PHP_MINOR_VERSION === 5 && version_compare(PHP_VERSION, '8.5.12', '>=')))) {
    http_response_code(503);
    exit('This website requires an updated PHP 8.4 (8.4.27+) or PHP 8.5 (8.5.12+).');
}

require_once './application/loader.php';
user::post_handler();
store::post_handler();
vote::post_handler();
require_once app_path . 'include/account-confirmation.php';
require_once base_path . 'template/' . get_config('template') . '/tpl/main.php';
