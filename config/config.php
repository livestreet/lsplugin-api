<?php

$config = array();

Config::Set('db.table.api_session', '___db.table.prefix___api_session');
Config::Set('router.page.api', 'PluginApi_ActionApi');

$config['path']['modules'] = Config::Get('path.root.server') . '/plugins/api/classes/modules/api/modules/';
$config['per_page'] = 10;

return $config;
