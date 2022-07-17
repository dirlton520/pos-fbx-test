<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_dashboard_statistics_per_employee extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170208144006_dashboard_statistics_per_employee.sql'));
				if (!$this->config->item('hide_dashboard_statistics'))
				{
					$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170208144006_dashboard_statistics_per_employee_grant.sql'));
				}

				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170208144006_dashboard_statistics_per_employee_drop_app_config_key.sql'));
		  }

	    public function down() 
			{
	    }

	}