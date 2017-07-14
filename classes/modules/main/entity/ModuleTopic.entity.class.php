<?php

class PluginApi_ModuleMain_EntityModuleTopic extends PluginApi_ModuleMain_EntityModule
{
    protected function runList()
    {

        $aFilterFields = $this->makeFilterFields($this->getParamStr('fields'));
        if (!$aFilterFields) {
            throw new PluginApi_ModuleMain_ExceptionBadRequest('Need set filter fields');
        }
        $aResult = $this->Topic_GetTopicsGood($this->getPage(), $this->getPerPage());
        foreach ($aResult['collection'] as $i => $oItem) {
            $aResult['collection'][$i] = $this->filterObject($oItem, $aFilterFields, Config::Get('plugin.api.allow_fields.topic'));
        }
        return $aResult;
    }

    protected function runGet()
    {
        if ($sId = $this->getParam('id') and $oTopic = $this->Topic_GetTopicById($sId)) {
            /**
             * Проверяем права на просмотр топика
             */
            if (!$this->ACL_IsAllowShowTopic($oTopic, $this->getUserCurrent())) {
                throw new PluginApi_ModuleMain_ExceptionBadRequest('Not allow show topic');
            }

            $aFilterFields = $this->makeFilterFields($this->getParamStr('fields'));
            return $this->filterObject($oTopic, $aFilterFields, Config::Get('plugin.api.allow_fields.topic'));
        }
        throw new PluginApi_ModuleMain_ExceptionBadRequest('Topic not found');
    }

    protected function fieldProcessingPreview($mValue, $sField, $oObject, $aParams)
    {
        if (isset($aParams[0])) {
            $sSize = $aParams[0];
        } else {
            $sSize = '800x300crop';
        }
        return $oObject->getPreviewImageWebPath($sSize);
    }
}