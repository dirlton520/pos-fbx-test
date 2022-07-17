<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_allow_for_employee_display_column_customization_for_all_manage_tables extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170503111701_allow_for_employee_display_column_customization_for_all_manage_tables.sql'));
	    }

	    public function down() 
			{
	    }

	}