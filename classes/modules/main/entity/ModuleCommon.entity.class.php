<?php

class PluginApi_ModuleMain_EntityModuleCommon extends PluginApi_ModuleMain_EntityModule
{
    protected function runLogin()
    {
        $sLogin = $this->getParamStr('login');
        $sPassword = $this->getParamStr('password');
        if (!$sLogin or !$sPassword) {
            throw new PluginApi_ModuleMain_ExceptionBadRequest('Need provide user credentials');
        }

        if ($oSession = $this->PluginApi_Main_LoginApiUser($sLogin, $sPassword)) {
            $oUser = $oSession->getUser();
            return array(
                'user-key' => $oSession->getHash(),
                'id'       => $oSession->getUserId(),
                'login'    => $oUser ? $oUser->getLogin() : '',
            );
        } else {
            throw new PluginApi_ModuleMain_ExceptionBadRequest('Failed login');
        }
    }

    protected function runLogout()
    {
        $this->requireAuthorization();
        $sHash = $this->getParamStr('user-key');
        if ($this->PluginApi_Main_LogoutApiUser($sHash)) {
            return true;
        } else {
            throw new PluginApi_ModuleMain_ExceptionBadRequest('Failed logout');
        }
    }

    protected function runStatus()
    {
        $oUser = $this->PluginApi_Main_GetUserCurrent();
        return array(
            'authorized' => $oUser ? true : false
        );
    }
}