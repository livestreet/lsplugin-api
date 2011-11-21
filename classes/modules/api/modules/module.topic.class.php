<?php
class PluginApi_ModuleApi_Topic extends PluginApi_ModuleApi_Module {
	
	protected $_aActions = array(
		'read' => 'ActionRead',
		'top' => 'ActionTop',
		'new' => 'ActionNew',
		'blog' => 'ActionBlog',
		'personal' => 'ActionPersonal',
		'list' => 'ActionList',
		'vote' => 'ActionVote'
	);

	/**
	 * Получение топика по ID
	 *
	 * @return unknown
	 */
	protected function ActionRead() {
		if (!$this->getParam('id')) {
			throw new ExceptionApiRequestError($this->Lang_Get('system_error'));
		}
		$oTopic = $this->Topic_GetTopicById($this->getParam('id'));
		if (!$oTopic) {
			throw new ExceptionApiRequestError($this->Lang_Get('system_error'));
		}

		/**
		 * Дополнительные данные топика
		 */
		$oTopic->setTopicExtraArray(@unserialize($oTopic->getExtra()));
		if ($oTopic->getType()=='photoset') {
			$oPhotos=array();
			foreach ($oTopic->getPhotosetPhotos() as $oPhoto) {
				$oPhotos[]=$this->filterObject($oPhoto);
			}
			$oTopic->setPhotosetPhotos($oPhotos);
		}
		
		return $this->filterObject($oTopic,$this->makeFilterFields($this->getParam('fields')));
	}

	/**
	 * ТОП топиков в разрезе периодов
	 *
	 * @return unknown
	 */
	protected function ActionTop() {
		/**
		 * Определяем период ТОПа
		 */
		$iTimeDelta=$this->GetTimeDelta($this->getParam('period'));
		$sDate=date("Y-m-d H:00:00",time()-$iTimeDelta);
		/**
		 * Получаем список топиков
		 */			
		$aTopics=$this->Topic_GetTopicsRatingByDate($sDate,$this->_getPerPage());
		$aFilterFields=$this->makeFilterFields($this->getParam('fields'));
		foreach ($aTopics as $k => $oTopic) {
			$oTopic->setTopicExtraArray(@unserialize($oTopic->getExtra()));
			$aTopics[$k] = $this->filterObject($oTopic,$aFilterFields);
		}
		return array('collection'=>$aTopics,'count'=>count($aTopics));
	}

	/**
	 * Новые топики
	 *
	 * @return unknown
	 */
	protected function ActionNew() {
		$aFilterFields=$this->makeFilterFields($this->getParam('fields'));
		$aRes=$this->Topic_GetTopicsNew($this->_getPage(),$this->_getPerPage());		
		foreach ($aRes['collection'] as $k => $oTopic) {
			$oTopic->setTopicExtraArray(@unserialize($oTopic->getExtra()));
			$aRes['collection'][$k] = $this->filterObject($oTopic,$aFilterFields);
		}
		return $aRes;
	}

	/**
	 * Топики из коллективных блогов + топики из конкретного коллективного блога
	 *
	 * @return unknown
	 */
	protected function ActionBlog() {
		$sShowType=$this->getParam('show_type');
		if (!in_array($sShowType,array('good','bad','new'))) {
			$sShowType='good';
		}

		if ($this->getParam('blog_id')) {
			if (!($oBlog=$this->Blog_GetBlogById($this->getParam('blog_id')))) {
				throw new ExceptionApiRequestError($this->Lang_Get('system_error'));
			}
			/**
			 * Определяем права на отображение закрытого блога
			 */
			if($oBlog->getType()=='close' and (!$this->oUserCurrent or !in_array($oBlog->getId(),$this->Blog_GetAccessibleBlogsByUser($this->oUserCurrent)))) {
				$bCloseBlog=true;
			} else {
				$bCloseBlog=false;
			}

			if (!$bCloseBlog) {
				$aRes=$this->Topic_GetTopicsByBlog($oBlog,$this->_getPage(),$this->_getPerPage(),$sShowType);
			} else {
				throw new ExceptionApiRequestError($this->Lang_Get('blog_close_show'));
			}
		} else {
			$aRes=$this->Topic_GetTopicsCollective($this->_getPage(),$this->_getPerPage(),$sShowType);
		}

		$aFilterFields=$this->makeFilterFields($this->getParam('fields'));
		foreach ($aRes['collection'] as $k => $oTopic) {
			$oTopic->setTopicExtraArray(@unserialize($oTopic->getExtra()));
			$aRes['collection'][$k] = $this->filterObject($oTopic,$aFilterFields);
		}
		return $aRes;
	}

	/**
	 * Топики из персональных блогов
	 *
	 * @return unknown
	 */
	protected function ActionPersonal() {
		$sShowType=$this->getParam('show_type');
		if (!in_array($sShowType,array('good','bad','new'))) {
			$sShowType='good';
		}
		
		$aFilterFields=$this->makeFilterFields($this->getParam('fields'));
		$aRes=$this->Topic_GetTopicsPersonal($this->_getPage(),$this->_getPerPage(),$sShowType);
		foreach ($aRes['collection'] as $k => $oTopic) {
			$oTopic->setTopicExtraArray(@unserialize($oTopic->getExtra()));
			$aRes['collection'][$k] = $this->filterObject($oTopic,$aFilterFields);
		}
		return $aRes;
	}

	/**
	 * Список топиков
	 *
	 * @return unknown
	 */
	protected function ActionList() {
		//$aFilter = $this->_aParams;
		$aFilter['topic_publish'] = 1;
		
		$aFilterFields=$this->makeFilterFields($this->getParam('fields'));
		$aRes = $this->Topic_GetTopicsByFilter($aFilter,$this->_getPage(),$this->_getPerPage());
		foreach ($aRes['collection'] as $k => $oTopic) {
			$oTopic->setTopicExtraArray(@unserialize($oTopic->getExtra()));
			$aRes['collection'][$k] = $this->filterObject($oTopic,$aFilterFields);
		}
		return $aRes;
	}

	/**
	 * Голосование за топик
	 *
	 * @return unknown
	 */
	protected function ActionVote() {
		$this->_requireAuthorization();

		$oUserCurrent = $this->PluginApi_ModuleApi_getUserCurrent();

		if (!$this->getParam('id')) {
			throw new ExceptionApiBadRequest();
		}

		if (!($oTopic=$this->Topic_GetTopicById($this->getParam('id')))) {
			throw new ExceptionApiRequestError($this->Lang_Get('system_error'));
		}

		if ($oTopic->getUserId()==$oUserCurrent->getId()) {
			throw new ExceptionApiRequestError($this->Lang_Get('topic_vote_error_self'));
		}

		if ($oTopicVote=$this->Vote_GetVote($oTopic->getId(),'topic',$oUserCurrent->getId())) {
			throw new ExceptionApiRequestError($this->Lang_Get('topic_vote_error_already'));
		}

		if (strtotime($oTopic->getDateAdd())<=time()-Config::Get('acl.vote.topic.limit_time')) {
			throw new ExceptionApiRequestError($this->Lang_Get('topic_vote_error_time'));
		}

		$iValue=$this->getParam('value');
		if (!in_array($iValue,array('1','-1','0'))) {
			throw new ExceptionApiRequestError($this->Lang_Get('system_error'));
		}

		if (!$this->ACL_CanVoteTopic($oUserCurrent,$oTopic) and $iValue) {
			throw new ExceptionApiRequestError($this->Lang_Get('topic_vote_error_acl'));
		}

		$oTopicVote=Engine::GetEntity('Vote');
		$oTopicVote->setTargetId($oTopic->getId());
		$oTopicVote->setTargetType('topic');
		$oTopicVote->setVoterId($oUserCurrent->getId());
		$oTopicVote->setDirection($iValue);
		$oTopicVote->setDate(date("Y-m-d H:i:s"));
		$iVal=0;
		if ($iValue!=0) {
			$iVal=(float)$this->Rating_VoteTopic($oUserCurrent,$oTopic,$iValue);
		}
		$oTopicVote->setValue($iVal);
		$oTopic->setCountVote($oTopic->getCountVote()+1);
		if ($this->Vote_AddVote($oTopicVote) and $this->Topic_UpdateTopic($oTopic)) {
			/**
			 * Добавляем событие в ленту
			 */
			$this->Stream_write($oTopicVote->getVoterId(), 'vote_topic', $oTopic->getId());

			return array('rating'=>$oTopic->getRating());
		} else {
			throw new ExceptionApiRequestError($this->Lang_Get('system_error'));
		}
	}

	/**
	 * Переводит параметр в нужный период времени
	 *
	 * @return unknown
	 */
	protected function GetTimeDelta($sPeriod) {
		switch ($sPeriod) {
			case 'all':
				/**
				 * за последние 100 лет :)
				 */
				$iTimeDelta=60*60*24*350*100;
				break;
			case '30d':
				/**
				 * за последние 30 дней
				 */
				$iTimeDelta=60*60*24*30;
				break;
			case '7d':
				/**
				 * за последние 7 дней
				 */
				$iTimeDelta=60*60*24*7;
				break;
			case '24h':
				/**
				 * за последние 24 часа
				 */
				$iTimeDelta=60*60*24*1;
				break;
			default:
				$iTimeDelta=60*60*24*7;
				break;
		}
		return $iTimeDelta;
	}
}