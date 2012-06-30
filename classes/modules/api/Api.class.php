<?php

require_once Plugin::GetPath('api') . 'classes/modules/api/Module.class.php';
require_once Plugin::GetPath('api') . 'classes/exceptions/ExceptionApi.class.php';
require_once Plugin::GetPath('api') . 'classes/exceptions/BadRequest.class.php';
require_once Plugin::GetPath('api') . 'classes/exceptions/ModuleNotFound.class.php';
require_once Plugin::GetPath('api') . 'classes/exceptions/ActionNotFound.class.php';
require_once Plugin::GetPath('api') . 'classes/exceptions/NeedAuthorization.class.php';
require_once Plugin::GetPath('api') . 'classes/exceptions/RequestError.class.php';

class PluginApi_ModuleApi extends Module
{
	protected $oMapper;
	
	protected $oUserCurrent;
	
	const RESPONSE_TYPE_JSON = 1;
	const RESPONSE_TYPE_DUMP = 2;
	
	protected $_aModules = array(
		'blog' => 'PluginApi_ModuleApi_Blog',
		'common' => 'PluginApi_ModuleApi_Common',
		'profile' => 'PluginApi_ModuleApi_Profile',
		'topic' => 'PluginApi_ModuleApi_Topic',
		'comment' => 'PluginApi_ModuleApi_Comment',
		'stream' => 'PluginApi_ModuleApi_Stream',
		'feed' => 'PluginApi_ModuleApi_Feed',
	);

    protected $_aIncludePath = array();
	
	public function Init()
	{
		$this->oMapper = Engine::GetMapper(__CLASS__);
        $this->_aIncludePath[] = Plugin::GetPath('api');
	}
	
	public function run($sModule, $sAction, $aParams)
	{
		$oModule = $this->_includeModule($sModule);
		if (!$oModule) {
			throw new ExceptionApiModuleNotFound($sModule);
			return false;
		}
		$oModule->init($aParams);
		return $oModule->exec($sAction);
	}
	
	public function authorize($sLogin, $sPassword)
	{
		$oUser = $this->User_getUserByLogin($sLogin);
		if ($oUser && $oUser->getPassword() == func_encrypt($sPassword) && $oUser->getActivate()) {
			$sKey = $this->oMapper->createSession($oUser->getId());
			$sHash = $this->_encodeHash($sKey, $oUser->getPassword());
			$this->oUserCurrent = $oUser;
			return $sHash;
		} else {
			return false;
		}
	}
	
	public function logout($sHash) {
		if ($aRes=$this->getUserByHash($sHash)) {
			list($oUser, $aSession) = $aRes;
			$this->oUserCurrent = null;
			$this->User_SetUserCurrent(null);
			return $this->oMapper->deleteSession($aSession['key']);
		}
		return false;
	}

	public function authenticate($sHash) {
		if ($aRes=$this->getUserByHash($sHash)) {
			list($oUser, $aSession) = $aRes;
			$this->oUserCurrent = $oUser;
			$this->User_SetUserCurrent($oUser);
			return true;
		}
		return false;
	}

	public function getUserByHash($sHash) {
		list($sKey, $sPasswordHash) = $this->_decodeHash($sHash);
		if (!$sKey || !$sPasswordHash){
			return false;
		}
		$aSession = $this->oMapper->getSession($sKey);
		if (!$aSession) {
			return false;
		}
		$oUser = $this->User_getUserById($aSession['uid']);
		if (!$oUser) {
			return false;
		}
		if ($sPasswordHash != md5($oUser->getPassword())) {
			return false;
		}
		return array($oUser,$aSession);
	}
	
	public function getUserCurrent()
	{
		return $this->oUserCurrent;
	}
	
	protected function _includeModule($sModule)
	{
		if (!isset($this->_aModules[$sModule])) {
			return false;
		}

        $sFile = false;
        foreach ($this->_aIncludePath as $sIncludePath) {
		    $sFileCheck = $sIncludePath . 'classes/modules/api/modules/module.' . $sModule . '.class.php';
            if (file_exists($sFileCheck)) {
                $sFile = $sFileCheck;
                break;
            }
        }

		if (!$sFile) {
			return false;
		}
		
		require_once $sFile;

		return class_exists($this->_aModules[$sModule]) ? new $this->_aModules[$sModule]($this->oEngine) : false;
	}
	
	protected function _encodeHash($sKey, $sPasswordHash)
	{
		return $sKey . md5($sPasswordHash);
	}
	
	protected function _decodeHash($sHash)
	{
		$sKey = substr($sHash, 0, 10);
		$sPasswordHash = substr($sHash, 10);
		return array($sKey, $sPasswordHash);
	}
}