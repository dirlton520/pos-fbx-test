<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_edit_tier_permission extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190627124129_edit_tier_permission.sql'));
	    }

	    public function down() 
			{
	    }

	}