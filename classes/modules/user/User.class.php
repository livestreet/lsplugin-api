<?php

class PluginApi_ModuleUser extends PluginApi_Inherit_ModuleUser {
	
	/**
	 * Устанавливает текущего пользователя
	 *
	 * @param unknown_type $oUser
	 * @return unknown
	 */
	public function SetUserCurrent($oUser) {
		return $this->oUserCurrent=$oUser;
	}
}