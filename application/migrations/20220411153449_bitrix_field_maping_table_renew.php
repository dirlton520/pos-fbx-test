<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_bitrix_field_maping_table_renew extends MY_Migration 
	{

	    public function up() 
		{
			$this->execute_sql(realpath(dirname(__FILE__).'/'.'20220411153449_bitrix_field_maping_table_renew.sql'));
	    }

	    public function down() 
		{
	    }

	} 
