<?php

class PluginApi_ModuleApi_MapperApi extends Mapper {
	
	public function createSession($iUserId) {
		$sql = 'SELECT * FROM ' . Config::Get('db.table.api_session') . ' WHERE uid = ?d';
		$aSession = $this->oDb->selectRow($sql, $iUserId);
		if ($aSession) {
			return $aSession['key'];
		} else {
			$sKey = func_generator();
			$sql = 'INSERT INTO ' . Config::Get('db.table.api_session') . ' SET
				`uid` = ?d,  `key` = ?';
			$this->oDb->query($sql, $iUserId, $sKey);
			return $sKey; 
		}
		
	}
	
	public function getSession($sKey) {
		$sql = 'SELECT * FROM '. Config::Get('db.table.api_session') . ' WHERE `key` = ?';
		return $this->oDb->selectRow($sql, $sKey);
	}
	
	public function deleteSession($sKey) {
		$sql = 'DELETE FROM '. Config::Get('db.table.api_session') . ' WHERE `key` = ?';
		return $this->oDb->query($sql, $sKey);
	}
}