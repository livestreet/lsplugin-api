<?php

class PluginApi_ModuleMain_EntityModule extends Entity
{
    protected function getParam($sName, $mDefault = null)
    {
        $aParams = $this->getParams();
        if ($aParams and is_array($aParams) and array_key_exists($sName, $aParams)) {
            return $aParams[$sName];
        }
        return $mDefault;
    }

    protected function getParamStr($sName, $mDefault = '')
    {
        $sVal = $this->getParam($sName, $mDefault);
        if (is_string($sVal)) {
            return $sVal;
        }
        return (string)$mDefault;
    }

    protected function getParamInt($sName, $mDefault = 0)
    {
        $iVal = $this->getParam($sName, $mDefault);
        if (is_numeric($iVal)) {
            return (int)$iVal;
        }
        return (int)$mDefault;
    }

    protected function getPerPage()
    {
        $iPerPage = $this->getParamInt('per_page', Config::Get('plugin.api.per_page'));
        if ($iPerPage > 100) {
            $iPerPage = 100;
        }
        return $iPerPage;
    }

    protected function getPage()
    {
        $iPage = $this->getParamInt('page', 1);
        return $iPage > 0 ? $iPage : 1;
    }

    protected function requireAuthorization()
    {
        if (!$this->PluginApi_ModuleMain_GetUserCurrent()) {
            throw new PluginApi_ModuleMain_ExceptionNeedAuthorization();
        }
    }

    protected function getUserCurrent()
    {
        return $this->PluginApi_ModuleMain_GetUserCurrent();
    }

    public function run($sMethod)
    {
        $sMethod = str_replace(array('-', '/'), '_', $sMethod);
        $sMethod = 'run' . func_camelize($sMethod);
        if (method_exists($this, $sMethod)) {
            return call_user_func(array($this, $sMethod));
        }
        throw new PluginApi_ModuleMain_ExceptionMethodNotFound();
    }


    /**
     * Фильтрует список полей возвращаемые в массиве данных объекта
     *
     * @param Entity $oObject
     * @param array $aFilter array('id','title','user'=>array('login','mail'));
     * @param array $aAllowFields
     * @return array
     */
    protected function filterObject(Entity $oObject, $aFilter = null, $aAllowFields = array())
    {
        if (!is_array($aFilter) || !count($aFilter)) {
            return array();
        }

        $aFilter = $this->filterAllowFields($aFilter, $aAllowFields);
        /**
         * Составляем список корневых полей
         */
        $aFieldsRoot = array();
        foreach ($aFilter as $k => $v) {
            if (is_numeric($k)) {
                $aFieldsRoot[] = $v;
            } else {
                $aFieldsRoot[] = $k;
            }
        }

        $aResult = array();
        foreach ($aFieldsRoot as $sKey) {
            $sValue = $oObject->_getDataOne($sKey);

            if (is_object($sValue) && $sValue instanceOf Entity) {
                if (isset($aFilter[$sKey]) and isset($aAllowFields[$sKey])) {
                    $aResult[$sKey] = $this->filterObject($sValue, $aFilter[$sKey], $aAllowFields[$sKey]);
                }
            } else {
                if (isset($aAllowFields[$sKey]) and strpos($aAllowFields[$sKey], '#') === 0) {
                    $sValue = $this->fieldProcessing($sValue, $aAllowFields[$sKey], $sKey, $oObject);
                }
                $aResult[$sKey] = $sValue;
            }
        }
        return $aResult;
    }

    /**
     * Преобразует список полей в массив
     *
     * @param string $sFields "id,title,user[login,mail]" или "id,title,user.login,user.mail"
     * @return array
     */
    protected function makeFilterFields($sFields)
    {
        if (!$sFields or !is_string($sFields)) {
            return array();
        }
        $iCount = 1;
        while ($iCount) {
            $sFields = preg_replace_callback("#([^\[\],]*)\[(?=[^\[]*\])(.*?)\]#", function ($aMatch) {
                return $aMatch[1] . '.' . str_replace(',', ',' . $aMatch[1] . '.', $aMatch[2]);
            }, $sFields, -1, $iCount);
        }

        $aResult = array();
        $aFields = explode(',', $sFields);
        foreach ($aFields as $sField) {
            $aPart = explode('.', $sField);
            $aValue = array();

            for ($i = count($aPart) - 1; $i >= 0; $i--) {
                if ($i == count($aPart) - 1) {
                    $aValue = array($aPart[$i]);
                } else {
                    $aValue = array($aPart[$i] => $aValue);
                }
            }
            $aResult = array_merge_recursive($aResult, $aValue);
        }
        return $aResult;
    }

    protected function filterAllowFields($aFields, &$aAllowFields)
    {
        if (!$aAllowFields or !is_array($aAllowFields)) {
            return array();
        }
        func_array_simpleflip($aAllowFields, '');

        foreach ($aAllowFields as $sField => $mParams) {
            if ($mParams and is_string($mParams) and strpos($mParams, '#') === false) {
                if ($aFieldsSub = Config::Get('plugin.api.allow_fields.' . $mParams)) {
                    $aAllowFields[$sField] = $aFieldsSub;
                } else {
                    unset($aAllowFields[$sField]);
                }
            }
        }

        foreach ($aFields as $key => $sField) {
            if (is_array($sField)) {
                if (isset($aAllowFields[$key])) {
                    $aFields[$key] = $this->filterAllowFields($sField, $aAllowFields[$key]);
                } else {
                    unset($aFields[$key]);
                }
            } else {
                if (!isset($aAllowFields[$sField])) {
                    unset($aFields[$key]);
                }
            }
        }

        return $aFields;
    }

    protected function fieldProcessing($mValue, $sName, $sField, $oObject)
    {
        if ($sName == '#avatar') {
            $mValue = $this->Fs_GetPathWeb($mValue);
        } elseif ($sName == '#serialize') {
            $mValue = @unserialize($mValue);
        } else {
            /**
             * Кастоный обработчик внутри модуля
             */
            $oObjectCall = $this;
            $aNamePart = explode('.', substr($sName, 1));
            if (count($aNamePart) > 1) {
                /**
                 * В обработчике указан модуль, получаем его
                 */
                $sName = $aNamePart[1];
                if ($oModule = $this->PluginApi_Main_GetModuleByName($aNamePart[0])) {
                    $oObjectCall = $oModule;
                }
            } else {
                $sName = $aNamePart[0];
            }
            /**
             * Смотрим наличие параметров
             */
            list($sName, $aParams) = $this->parseProcessingParams($sName);
            $sMethod = 'fieldProcessing' . func_camelize($sName);
            if (method_exists($oObjectCall, $sMethod)) {
                $mValue = call_user_func(array($oObjectCall, $sMethod), $mValue, $sField, $oObject, $aParams);
            }
        }
        return $mValue;
    }

    protected function parseProcessingParams($sName)
    {
        if (preg_match('#^(.+)\((.+)\)$#i', $sName, $aMatch)) {
            return array($aMatch[1], explode(',', $aMatch[2]));
        }
        return array($sName, array());
    }
}