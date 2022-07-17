<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_remove_is_bogo_from_database extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170330110341_remove_is_bogo_from_database.sql'));
	    }

	    public function down() 
			{
	    }

	}