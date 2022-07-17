<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_login_time_restrictions extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200227091741_login_time_restrictions.sql'));
	    }

	    public function down() 
			{
	    }

	}