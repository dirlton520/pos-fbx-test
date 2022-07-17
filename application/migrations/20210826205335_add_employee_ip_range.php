<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_add_employee_ip_range extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210826205335_add_employee_ip_range.sql'));
	    }

	    public function down() 
			{
	    }

	}