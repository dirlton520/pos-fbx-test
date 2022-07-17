<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_employee_permission_templates extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200701093459_employee_permission_templates.sql'));
	    }

	    public function down() 
			{
	    }

	}