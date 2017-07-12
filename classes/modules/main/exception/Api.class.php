<?php

class PluginApi_ModuleMain_ExceptionApi extends Exception
{
    protected $message = 'api error';
    protected $code = PluginApi_ModuleMain::API_ERROR_CODE_OTHER;

    public function getMessageApi()
    {
        if ($sMsg = $this->getMessage()) {
            return $sMsg;
        }
        return get_class($this);
    }
}