<?php
class PluginApi_ModuleApi_Common extends PluginApi_ModuleApi_Module {
	protected $_aActions = array(
	    'login' => 'ActionLogin',
	    'logout' => 'ActionLogout',
	    'status' => 'ActionStatus',
	    'registration' => 'ActionRegistration',
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
		    if ($aRes = $this->PluginApi_ModuleApi_getUserByHash($sHash)) {
			    list($oUser, $aSession) = $aRes;
			    return array('hash'=>$sHash, 'id'=>$oUser->getId(), 
					 'login' => $oUser->getLogin(), 'name' => $oUser->getName());
		    }
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

    /**
     * Обработка регистрации из приложения.
     */
    protected function ActionRegistration() {
	/**
	 * Создаем объект пользователя и устанавливаем сценарий валидации
	 */
	$oUser=Engine::GetEntity('ModuleUser_EntityUser');
	$oUser->_setValidateScenario('registration');

	/**
	 * Не нуждаемся в капче от приложения, поэтому используем фейковую.
	 */
	$_SESSION['captcha_keystring'] = '123';
		
	/**
	 * Заполняем поля (данные)
	 */
	$oUser->setLogin($this->getParam('login'));
	$oUser->setMail($this->getParam('mail'));
	$oUser->setPassword($this->getParam('password'));
	$oUser->setPasswordConfirm($this->getParam('password_confirm'));
	$oUser->setCaptcha('123');
	$oUser->setDateRegister(date("Y-m-d H:i:s"));
	$oUser->setIpRegister(func_getIp());
	/**
	 * Если используется активация, то генерим код активации
	 */
	if (Config::Get('general.reg.activation')) {
		$oUser->setActivate(0);
		$oUser->setActivateKey(md5(func_generator().time()));
	} else {
		$oUser->setActivate(1);
		$oUser->setActivateKey(null);
	}
	
	$this->Hook_Run('registration_validate_before', array('oUser'=>$oUser));
	/**
	 * Запускаем валидацию
	 */
	if ($oUser->_Validate()) {
		$this->Hook_Run('registration_validate_after', array('oUser'=>$oUser));
		$oUser->setPassword(md5($oUser->getPassword()));
		if ($this->User_Add($oUser)) {
			$this->Hook_Run('registration_after', array('oUser'=>$oUser));
			/**
			 * Убиваем каптчу
			 */
			unset($_SESSION['captcha_keystring']);
			/**
			 * Подписываем пользователя на дефолтные события в ленте активности
			 */
			$this->Stream_switchUserEventDefaultTypes($oUser->getId());
			/**
			 * Если юзер зарегистрировался по приглашению то обновляем инвайт
			 */
			if (Config::Get('general.reg.invite') and $oInvite=$this->User_GetInviteByCode($this->GetInviteRegister())) {
				$oInvite->setUserToId($oUser->getId());
				$oInvite->setDateUsed(date("Y-m-d H:i:s"));
				$oInvite->setUsed(1);
				$this->User_UpdateInvite($oInvite);
			}
			/**
			 * Если стоит регистрация с активацией то проводим её
			 */
			if (Config::Get('general.reg.activation')) {
				/**
				 * Отправляем на мыло письмо о подтверждении регистрации
				 */
				$this->Notify_SendRegistrationActivate($oUser,getRequestStr('password'));
				$this->Viewer_AssignAjax('sUrlRedirect',Router::GetPath('registration').'confirm/');
			} else {
				$this->Notify_SendRegistration($oUser,getRequestStr('password'));
				$oUser=$this->User_GetUserById($oUser->getId());
			}
			
			return true;
		} else {
			$this->Message_AddErrorSingle($this->Lang_Get('system_error'));
		}
	} else {
		/**
		 * Получаем ошибки
		 */
		$this->Viewer_AssignAjax('aErrors', $oUser->_getValidateErrors());
	}
    }
}
