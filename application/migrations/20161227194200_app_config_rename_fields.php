<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_app_config_rename_fields extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/20161227194200_app_config_rename_fields.sql'));
	    }

	    public function down() 
			{
	    }

	}