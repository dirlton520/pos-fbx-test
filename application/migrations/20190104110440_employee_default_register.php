<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_employee_default_register extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190104110440_employee_default_register.sql'));
	    }

	    public function down() 
			{
	    }

	}