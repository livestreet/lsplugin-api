<?php
class PluginApi_ModuleApi_Stream extends PluginApi_ModuleApi_Module {
	protected $_aActions = array(
		'list' => 'ActionList',
		'more' => 'ActionMore',
	);

	protected function ActionList() {
		$this->_requireAuthorization();

		return $this->ReadStream(null);
	}

	protected function ActionMore() {
		$this->_requireAuthorization();

		$iFromId = $this->getParam('from_id');
		if (!is_numeric($iFromId))  {
			throw new ExceptionApiRequestError($this->Lang_Get('system_error'));
		}
		return $this->ReadStream($iFromId);
	}

	protected function ReadStream($iFromId) {
		$aFilterFields=$this->makeFilterFields($this->getParam('fields'));
		/**
		 * Читаем события
		 */
		$aEvents = $this->Stream_Read(null,$iFromId);
		$bDisableGetMoreButton=count($aEvents) < Config::Get('module.stream.count_default');
		$iStreamLastId=0;
		if (count($aEvents)) {
			$oEvenLast=end($aEvents);
			$iStreamLastId=$oEvenLast->getId();
		}

		foreach ($aEvents as $k => $oEvent) {
			$aEvents[$k] = $this->filterObject($oEvent,$aFilterFields);
		}
		return array('collection'=>$aEvents,'count'=>count($aEvents),'bDisableGetMoreButton'=>$bDisableGetMoreButton,'iStreamLastId'=>$iStreamLastId);
	}
}