<?php

class PluginApi_ModuleMain_ExceptionBadRequest extends PluginApi_ModuleMain_ExceptionApi
{
    protected $message = 'bad request';
    protected $code = PluginApi_ModuleMain::API_ERROR_CODE_BAD_REQUEST;
}