<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_tag_permission_template_to_employee extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200711111404_tag_permission_template_to_employee.sql'));
	    }

	    public function down() 
			{
	    }

	}