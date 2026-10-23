<?php

// Isolated HTTP-test configuration. Never use real service credentials here.
require __DIR__ . '/../../application/config/config.php.sample';
$config['baseurl'] = 'https://example.invalid';
$config['debug_mode'] = false;
$config['template'] = 'advance';
$config['language'] = 'english';
$config['script_version'] = '2.0.4';
$config['store_enabled'] = true;
$config['allow_signup'] = true;
$config['invite_code'] = 'test-invite';
$config['captcha_type'] = 0;
$config['soap_for_register'] = false;
$config['srp6_support'] = true;
$config['battlenet_support'] = false;
$config['disable_top_players'] = true;
$config['disable_online_players'] = true;
$config['db_auth_host'] = '127.0.0.1';
$config['db_auth_user'] = 'unused';
$config['db_auth_pass'] = 'unused';
$config['db_auth_dbname'] = 'unused';
$config['realmlists'] = [];
