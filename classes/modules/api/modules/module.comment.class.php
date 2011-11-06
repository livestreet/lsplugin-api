<?php
class PluginApi_ModuleApi_Comment extends PluginApi_ModuleApi_Module {
	
	protected $_aActions = array ('list' => 'ActionList', 'add' => 'ActionAdd', 'vote' => 'ActionVote' );
	
	/**
	 * Возвращает список комментариев к объекту
	 * @throws ExceptionApiRequestError
	 */
	protected function ActionList() {
		if (! $this->getParam ( 'id' ) || ! $this->getParam ( 'type' )) {
			throw new ExceptionApiRequestError ( $this->Lang_Get ( 'system_error' ) );
		}
		
		$iId = $this->getParam ( 'id' );
		$sType = $this->getParam ( 'type' );
		if (! in_array ( $sType, array ('topic' ) )) {
			throw new ExceptionApiRequestError ( $this->Lang_Get ( 'system_error' ) );
		}
		
		$bOk = FALSE;
		switch ($sType) {
			case 'topic' :
				$oObject = $this->Topic_GetTopicById ( $iId );
				if ($oObject && $oObject->getPublish ()) {
					$bOk = TRUE;
				}
				break;
			
			default :
				$oObject = null;
				
				break;
		}
		
		if (! $bOk) {
			throw new ExceptionApiRequestError ( $this->Lang_Get ( 'system_error' ) );
		}
		
		$aReturn = $this->Comment_GetCommentsByTargetId ( $iId, $sType );
		
		$aFilterFields = $this->makeFilterFields ( $this->getParam ( 'fields' ) );
		$aComments = $aReturn ['comments'];
		foreach ( $aComments as $k => $oComment ) {
			$aComments [$k] = $this->filterObject ( $oComment, $aFilterFields );
		}
		
		return array ('collection' => $aComments, 'count' => count ( $aComments ) );
	}
	
	protected function ActionAdd() {
	
	}
	
	protected function ActionVote() {
	
	}
}