<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_edit_and_delete_register_log_permissions extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190606092507_edit_and_delete_register_log_permissions.sql'));
	    }

	    public function down() 
			{
	    }

	}