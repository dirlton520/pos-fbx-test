<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_edit_profile_permission extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/20161227194630_edit_profile_permission.sql'));
	    }

	    public function down() 
			{
	    }

	}