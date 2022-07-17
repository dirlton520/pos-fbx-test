<?php
class MY_Session_database_driver extends CI_Session_database_driver {

	public function gc($maxlifetime)
	{
		parent::gc($maxlifetime);
		date_default_timezone_set('America/New_York');
		if ($this->_db->table_exists('app_files') && $this->_db->field_exists('expires','app_files'))
		{		
			return $this->_db->delete('app_files', 'expires < '.$this->_db->escape(date('Y-m-d H:i:s')).' and expires IS NOT NULL');
		}
		
		return TRUE;
	}
}