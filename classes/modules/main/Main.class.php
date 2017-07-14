<?php

require_once(__DIR__ . '/exception/Api.class.php');
require_once(__DIR__ . '/exception/BadRequest.class.php');
require_once(__DIR__ . '/exception/MethodNotFound.class.php');
require_once(__DIR__ . '/exception/ModuleNotFound.class.php');
require_once(__DIR__ . '/exception/NeedAuthorization.class.php');

class PluginApi_ModuleMain extends ModuleORM
{
    const API_ERROR_CODE_MODULE_NOT_FOUND = 1;
    const API_ERROR_CODE_METHOD_NOT_FOUND = 2;
    const API_ERROR_CODE_NEED_AUTHORIZATION = 3;
    const API_ERROR_CODE_BAD_REQUEST = 4;
    const API_ERROR_CODE_OTHER = 5;

    protected $oUserCurrent = null;


    public function GetUserCurrent()
    {
        return $this->oUserCurrent;
    }

    public function SetUserCurrent($oUser)
    {
        $this->oUserCurrent = $oUser;
        $this->User_SetUserCurrent($oUser);
    }

    public function LoginApiUser($sLogin, $sPassword)
    {
        if ($oUser = $this->User_GetUserByLogin($sLogin) and $this->User_VerifyPassword($sPassword, $oUser->getPassword())) {
            if ($oUser->getActivate()) {
                /**
                 * Без ограничений создаем новую API сессию
                 */
                $oSession = Engine::GetEntity('PluginApi_ModuleMain_EntitySession');
                $oSession->setUserId($oUser->getId());
                $oSession->setHash(func_generator(32));
                $oSession->add();
                $oSession->setUser($oUser);
                return $oSession;
            }
        }
        return false;
    }

    public function LogoutApiUser($sHash)
    {
        if ($sHash and $oSession = $this->GetSessionByHash($sHash) and $oUser = $oSession->getUser()) {
            $oSession->delete();
            return true;
        }
        return false;
    }

    public function AuthApiUser($sHash)
    {
        if ($sHash and $oSession = $this->GetSessionByHash($sHash) and $oUser = $oSession->getUser() and $oUser->getActivate()) {
            $this->SetUserCurrent($oUser);
            return true;
        }
        return false;
    }

    public function GetModuleByName($sName)
    {
        $sName = str_replace(array('-'), '_', $sName);
        $sModuleClass = 'PluginApi_ModuleMain_EntityModule' . func_camelize($sName);

        if (!class_exists($sModuleClass)) {
            /**
             * Поддержка модулей от других плагинов
             */
            $sModuleClass = Engine::getInstance()->Plugin_GetDelegate('entity', $sModuleClass);
        }
        if (class_exists($sModuleClass)) {
            $oModule = Engine::GetEntity($sModuleClass);
            $oModule->setParams($_REQUEST);
            return $oModule;
        }
        return null;
    }
}