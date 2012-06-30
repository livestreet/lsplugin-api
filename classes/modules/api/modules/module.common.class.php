<?php
class PluginApi_ModuleApi_Common extends PluginApi_ModuleApi_Module {
	protected $_aActions = array(
		'login' => 'ActionLogin',
        'logout' => 'ActionLogout',
        'status' => 'ActionStatus',
    );

	/**
	 * Авторизация
	 *
	 * @return unknown
	 */
	protected function ActionLogin() {
		if (!$this->getParam('login') || !$this->getParam('password')) {
			throw new ExceptionApiRequestError($this->Lang_Get('system_error'));
		}

		if ($sHash=$this->PluginApi_ModuleApi_authorize($this->getParam('login'), $this->getParam('password'))) {
			return array('hash'=>$sHash);
		}
		throw new ExceptionApiRequestError($this->Lang_Get('user_login_bad'));
	}

	/**
	 * Разлогинивание (выход)
	 *
	 * @return unknown
	 */
	protected function ActionLogout() {
		return array('result'=>(int)$this->PluginApi_ModuleApi_logout($this->getParam('hash')));
	}

    /**
     * Получаем список модулей, и залогинен ли пользователь
     *
     * @return array
     */
    protected function ActionStatus() {
        return $this->PluginApi_ModuleApi_status();
    }

}