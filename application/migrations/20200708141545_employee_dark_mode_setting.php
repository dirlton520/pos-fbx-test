<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_employee_dark_mode_setting extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200708141545_employee_dark_mode_setting.sql'));
	    }

	    public function down() 
			{
	    }

	}