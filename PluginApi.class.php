<?php

if (!class_exists('Plugin')) {
	die('Hacking attempt!');
}

class PluginApi extends Plugin {
	protected $aInherits=array(
		'module'=>array('ModuleUser'=>'PluginApi_ModuleUser'),
	);

	public function Activate() {
		if (!$this->isTableExists('prefix_api_session')) {
			/**
			 * При активации выполняем SQL дамп
			 */
			$this->ExportSQL(dirname(__FILE__).'/sql/create.sql');
		}
		return true;
	}

	public function Init(){}
}
