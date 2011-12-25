<?php
class PluginApi_ModuleApi_Feed extends PluginApi_ModuleApi_Module
{
	protected $_aActions = array(
		'list' => 'ActionList',
		'more' => 'ActionMore',
	);

	protected function ActionList() {
		$this->_requireAuthorization();

		return $this->ReadFeed(null);
	}

	protected function ActionMore() {
		$this->_requireAuthorization();

		$iFromId = $this->getParam('from_id');
		if (!is_numeric($iFromId))  {
			throw new ExceptionApiRequestError($this->Lang_Get('system_error'));
		}
		return $this->ReadFeed($iFromId);
	}

	protected function ReadFeed($iFromId) {
		$oUserCurrent = $this->PluginApi_ModuleApi_getUserCurrent();
		$aFilterFields=$this->makeFilterFields($this->getParam('fields'));
		/**
		 * Читаем ленту топиков
		 */
		$aTopics = $this->Userfeed_read($oUserCurrent->getId(),null,$iFromId);
		$bDisableGetMoreButton=count($aTopics) < Config::Get('module.userfeed.count_default');
		$iUserfeedLastId=0;
		if (count($aTopics)) {
			$iUserfeedLastId=end($aTopics)->getId();
		}

		foreach ($aTopics as $k => $oTopic) {
			$aTopics[$k] = $this->filterObject($oTopic,$aFilterFields);
		}
		return array('collection'=>$aTopics,'count'=>count($aTopics),'bDisableGetMoreButton'=>$bDisableGetMoreButton,'iUserfeedLastId'=>$iUserfeedLastId);
	}
}