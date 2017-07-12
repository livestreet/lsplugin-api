<?php

class PluginApi_ModuleMain_ExceptionMethodNotFound extends PluginApi_ModuleMain_ExceptionApi
{
    protected $message = 'method not found';
    protected $code = PluginApi_ModuleMain::API_ERROR_CODE_METHOD_NOT_FOUND;
}