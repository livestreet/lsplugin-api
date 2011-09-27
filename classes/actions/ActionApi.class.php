<?php
class PluginApi_ActionApi extends ActionPlugin 
{
	protected $_aParams;
	protected $_iResponseType = PluginApi_ModuleApi::RESPONSE_TYPE_DUMP;
	
	protected $_aResponse = array();
	protected $_bStateError;
	protected $_sError;
	
	public function Init()
	{
		$this->_aParams = $_REQUEST;
		$this->_authenticate();
		$this->_handleParams();
	}
	
	protected function RegisterEvent() 
	{
		$this->AddEventPreg('/^[a-z_]*$/i','EventIndex');
	}
	
	protected function EventIndex()
	{
		$sModule = $this->sCurrentEvent;
		$sAction = $this->GetParam(0);
		try {
			if (!$sModule || !$sAction) {
				throw new ExceptionApiBadRequest($sModule);
			}
			$this->_aResponse = $this->PluginApi_ModuleApi_run($sModule, $sAction, $this->_aParams);
		} catch (ExceptionApiRequestError $e){
			$this->_bStateError = true;
			$this->_sError = $e->getMessage();
			//echo $e->getMessage();
		} catch (ExceptionApiNeedAuthorization $e){
			$this->_bStateError = true;
			$this->_sError = get_class($e);
		} catch (ExceptionApi $e) {
			$this->_bStateError = true;
			//var_dump($e);
			$this->_sError = get_class($e);
		}
	}
	
	protected function _authenticate()
	{
		if (getRequest('hash')) {
			$this->PluginApi_ModuleApi_authenticate(getRequest('hash'));
		}
	}
	
	protected function _handleParams()
	{
		/**
		 * Тип ответа
		 */
		if (!empty($this->_aParams['response_type'])) {
			switch ($this->_aParams['response_type']) {
				case 'json':
					$this->_iResponseType = PluginApi_ModuleApi::RESPONSE_TYPE_JSON;
					break;
				default:
					$this->_iResponseType = PluginApi_ModuleApi::RESPONSE_TYPE_JSON;
					break;
			}
			unset ($this->_aParams['response_type']);
		}
	}
	
	public function EventShutdown()
	{
		switch ($this->_iResponseType) {
			case PluginApi_ModuleApi::RESPONSE_TYPE_JSON:
				$this->Viewer_SetResponseAjax('json',true,false);
				if (!$this->_bStateError) {
					$this->Viewer_AssignAjax('response', $this->_aResponse);
				} else {
					$this->Message_AddError($this->_sError, 'error');
				}
				break;
			case PluginApi_ModuleApi::RESPONSE_TYPE_DUMP:
				if (!$this->_bStateError) {
					var_dump($this->_aResponse);
				} else {					
					var_dump(array('error'=>$this->_sError));
				}
				exit();
				break;
		}
	}
}