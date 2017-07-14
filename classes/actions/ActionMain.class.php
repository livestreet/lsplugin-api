<?php

/**
 * LiveStreet CMS
 * Copyright © 2013 OOO "ЛС-СОФТ"
 *
 * ------------------------------------------------------
 *
 * Official site: www.livestreetcms.com
 * Contact e-mail: office@livestreetcms.com
 *
 * GNU General Public License, version 2:
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * ------------------------------------------------------
 *
 * @link http://www.livestreetcms.com
 * @copyright 2013 OOO "ЛС-СОФТ"
 * @author Maxim Mzhelskiy <rus.engine@gmail.com>
 *
 */
class PluginApi_ActionMain extends ActionPlugin
{

    protected $_aResponse = array();
    protected $_bErrorState = false;
    protected $_sErrorMsg = '';
    protected $_iErrorCode = 0;

    public function Init()
    {

    }

    /**
     * Регистрируем евенты
     *
     */
    protected function RegisterEvent()
    {
        $this->AddEventPreg('/^[\w\-\_]{1,30}$/i', 'EventRequest');
    }

    /**
     * Запрос к API
     */
    protected function EventRequest()
    {
        $this->SetTemplate(false);

        $sModule = $this->sCurrentEvent;
        $sMethod = join('_', $this->GetParams());
        try {
            if ($sApiKey = Config::Get('plugin.api.api_key')) {
                if ($sApiKey != getRequestStr('api-key')) {
                    throw new PluginApi_ModuleMain_ExceptionApi('wrong API key');
                }
            }
            /**
             * Авторизуем, если передан ключ пользователя
             */
            if ($sUserKey = getRequestStr('user-key')) {
                $this->PluginApi_Main_AuthApiUser($sUserKey);
            }
            if ($oModule = $this->PluginApi_Main_GetModuleByName($sModule)) {
                $this->_aResponse = $oModule->run($sMethod);
            } else {
                throw new PluginApi_ModuleMain_ExceptionModuleNotFound();
            }
        } catch (PluginApi_ModuleMain_ExceptionApi $e) {
            $this->_aResponse = array();
            $this->_bErrorState = true;
            $this->_sErrorMsg = $e->getMessageApi();
            $this->_iErrorCode = $e->getCode();
        } catch (Exception $e) {
            $this->_aResponse = array();
            $this->_bErrorState = true;
            $this->_sErrorMsg = $e->getMessage();
            $this->_iErrorCode = $e->getCode();
        }
    }

    protected function EventNotFound()
    {
        $this->_bErrorState = true;
        $this->_sErrorMsg = 'module not found';
        $this->_iErrorCode = PluginApi_ModuleMain::API_ERROR_CODE_MODULE_NOT_FOUND;
    }

    public function EventShutdown()
    {
        $this->Viewer_SetResponseAjax('json', true, false);
        if (!$this->_bErrorState) {
            $this->Viewer_AssignAjax('response', $this->_aResponse);
        } else {
            $this->Message_AddError($this->_sErrorMsg);
            $this->Viewer_AssignAjax('iCodeError', $this->_iErrorCode);
        }
    }
}