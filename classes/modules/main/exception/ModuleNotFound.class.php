<?php

class PluginApi_ModuleMain_ExceptionModuleNotFound extends PluginApi_ModuleMain_ExceptionApi
{
    protected $message = 'module not found';
    protected $code = PluginApi_ModuleMain::API_ERROR_CODE_MODULE_NOT_FOUND;
}