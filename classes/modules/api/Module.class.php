<?php

abstract class PluginApi_ModuleApi_Module
{
	protected $oEngine=null;

	protected $_aParams;
	protected $_aActions = array();

	final public function __construct(Engine $oEngine) {
		$this->oEngine=$oEngine;
	}

	public function __call($sName,$aArgs) {
		return $this->oEngine->_CallModule($sName,$aArgs);
	}

	public function exec($sAction)
	{
		if (empty($this->_aActions[$sAction])) {
			throw new ExceptionApiActionNotFound($sAction);
		}
		return call_user_func(array($this, $this->_aActions[$sAction]));
	}

	public function init($aParams)
	{
		$this->_aParams = $aParams;

		foreach ($this->_aActions as $sMethod) {
			if (!method_exists($this, $sMethod)) {
				throw new ExceptionApiActionNotFound($sMethod);
			}
		}
	}

	protected function _requireAuthorization()
	{
		if (!$this->PluginApi_ModuleApi_getUserCurrent()) {
			throw new ExceptionApiNeedAuthorization();
		}
	}

	protected function _getPage() {
		if (preg_match("#^\d+$#",$this->getParam('page'))) {
			return $this->getParam('page');
		}
		return 1;
	}
	
	protected function _getFromId() {
		return $this->getParam('from_id',1);
	}

	protected function _getPerPage() {
		if (preg_match("#^\d+$#",$this->getParam('per_page'))) {
			return $this->getParam('per_page');
		}
		return Config::Get('plugin.api.per_page');
	}

	protected function getParam($sName,$default=null) {
		return (isset($this->_aParams[$sName])) ? $this->_aParams[$sName] : $default;
	}
}