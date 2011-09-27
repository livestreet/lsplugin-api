<?php
class PluginApi_ModuleApi_Blog extends PluginApi_ModuleApi_Module
{
	protected $_aActions = array(
		'list' => 'ActionList',
		'read' => 'ActionRead',
		'vote' => 'ActionVote',
	);
	
	protected function ActionRead()
	{
		if (empty($this->_aParams['id']) ) {
			throw new ExceptionApiRequestError($this->Lang_Get('system_error'));
		}
		$oBlog = $this->Blog_GetBlogById($this->_aParams['id']);
		if (!$oBlog) {
			throw new ExceptionApiRequestError($this->Lang_Get('system_error'));
		}
		
		return $oBlog->_getDataArray();
	}
	
	protected function ActionList()
	{
		$this->Blog_GetBlogsRating($this->_getPage(), $this->_getPerPage());	
		foreach($aBlogs['collection'] as $i => $oBlog) {
			$aBlogs['collection'][$i] = $oBlog->_getDataArray();
		}
		
		return $aBlogs;
	}
	
	protected function ActionVote()
	{
		$this->_requireAuthorization();
		
		$oUserCurrent = $this->PluginApi_ModuleApi_getUserCurrent();
		
		if (empty($this->_aParams['id']) || empty($this->_aParams['value'])) {
			throw new ExceptionApiBadRequest();
		}
		
		if (!($oBlog=$this->Blog_GetBlogById($this->_aParams['id']))) {
			throw new ExceptionApiRequestError($this->Lang_Get('system_error'));
		}

		if ($oBlog->getOwnerId()==$oUserCurrent->getId()) {
			throw new ExceptionApiRequestError($this->Lang_Get('blog_vote_error_self'));
		}

		if ($oBlogVote=$this->Vote_GetVote($oBlog->getId(),'blog',$oUserCurrent->getId())) {
			throw new ExceptionApiRequestError($this->Lang_Get('blog_vote_error_already'));
		}

		switch($this->ACL_CanVoteBlog($oUserCurrent,$oBlog)) {
			case ModuleACL::CAN_VOTE_BLOG_TRUE:
				$iValue=$this->_aParams['value'];
				if (in_array($iValue,array('1','-1'))) {
					$oBlogVote=Engine::GetEntity('Vote');
					$oBlogVote->setTargetId($oBlog->getId());
					$oBlogVote->setTargetType('blog');
					$oBlogVote->setVoterId($oUserCurrent->getId());
					$oBlogVote->setDirection($iValue);
					$oBlogVote->setDate(date("Y-m-d H:i:s"));
					$iVal=(float)$this->Rating_VoteBlog($oUserCurrent,$oBlog,$iValue);
					$oBlogVote->setValue($iVal);
					$oBlog->setCountVote($oBlog->getCountVote()+1);
					if ($this->Vote_AddVote($oBlogVote) and $this->Blog_UpdateBlog($oBlog)) {
						$this->Viewer_AssignAjax('iCountVote',$oBlog->getCountVote());
						$this->Viewer_AssignAjax('iRating',$oBlog->getRating());
						 /**
						 * Добавляем событие в ленту
						 */
						$this->Stream_write($oBlogVote->getVoterId(), 'vote_blog', $oBlog->getId());
					} else {
						throw new ExceptionApiRequestError($this->Lang_Get('system_error'));
					}
				} else {
					throw new ExceptionApiRequestError($this->Lang_Get('system_error'));
				}
				break;
			case ModuleACL::CAN_VOTE_BLOG_ERROR_CLOSE:
				throw new ExceptionApiRequestError($this->Lang_Get('blog_vote_error_close'));
				break;
			case ModuleACL::CAN_VOTE_BLOG_FALSE:
				throw new ExceptionApiRequestError($this->Lang_Get('blog_vote_error_acl'));
				break;
		}
	}
}