<?php

class PluginApi_ModuleMain_ExceptionNeedAuthorization extends PluginApi_ModuleMain_ExceptionApi
{
    protected $message = 'need authorization';
    protected $code = PluginApi_ModuleMain::API_ERROR_CODE_NEED_AUTHORIZATION;
}