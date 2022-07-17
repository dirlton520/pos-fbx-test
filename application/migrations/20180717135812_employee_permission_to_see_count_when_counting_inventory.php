<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_employee_permission_to_see_count_when_counting_inventory extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180717135812_employee_permission_to_see_count_when_counting_inventory.sql'));
	    }

	    public function down() 
			{
	    }

	}