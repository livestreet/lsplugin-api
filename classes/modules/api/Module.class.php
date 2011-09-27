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
	
	/**
	 * Фильтрует список полей возвращаемые в массиве данных объекта
	 *
	 * @param Entity $oObject
	 * @param unknown_type $aFilter, array('id','title','user'=>array('login','mail'));
	 * @return unknown
	 */
	protected function filterObject(Entity $oObject,$aFilter=null) {
		if (!is_array($aFilter) || !count($aFilter)) {
			return $oObject->_getDataArray();
		}
		
		/**
		 * Составляем список корневых полей
		 */
		$aFieldsRoot=array();
		foreach ($aFilter as $k => $v) {
			if (is_numeric($k)) {
				$aFieldsRoot[]=$v;
			} else {
				$aFieldsRoot[]=$k;
			}
		}
				
		$aResult = array();
		foreach ($oObject->_getData() as $sKey => $sValue) {
			if (!in_array($sKey,$aFieldsRoot)) {
				continue;
			}
			if (is_object($sValue) && $sValue instanceOf Entity) {
				$aResult[$sKey] = isset($aFilter[$sKey]) ? $this->filterObject($sValue,$aFilter[$sKey]) : $this->filterObject($sValue);
			} else {
				$aResult[$sKey] = $sValue;
			}
		}
		return $aResult;
	}
	
	/**
	 * Преобразует список полей в массив
	 *
	 * @param unknown_type $sFields, "id,title,user[login,mail]" или "id,title,user.login,user.mail]"
	 */
	protected function makeFilterFields($sFields) {
		if (!$sFields) {
			return array();
		}
		$iCount=1;
		while ($iCount) {
			$sFields=preg_replace_callback("#([^\[\],]*)\[(?=[^\[]*\])(.*?)\]#",array($this,'makeFilterFieldsCallback'),$sFields,-1,$iCount);
		}
		
		$aResult=array();
		$aFields=explode(',',$sFields);
		foreach ($aFields as $sField) {
			$aPart=explode('.',$sField);
			$aValue=array();
			
			for ($i=count($aPart)-1;$i>=0;$i--) {
				if ($i==count($aPart)-1) {
					$aValue=array($aPart[$i]);
				} else {
					$aValue=array($aPart[$i]=>$aValue);
				}
			}
			$aResult=array_merge_recursive($aResult,$aValue);
		}
		return $aResult;
	}
	
	protected function makeFilterFieldsCallback($aMatch) {
		return $aMatch[1].'.'.str_replace(',',','.$aMatch[1].'.',$aMatch[2]);
	}
}