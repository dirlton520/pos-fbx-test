<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_bitrix_file_id_app_file extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20220411153439_bitrix_file_id_app_file.sql'));
	    }

	    public function down() 
			{
	    }

	} 
