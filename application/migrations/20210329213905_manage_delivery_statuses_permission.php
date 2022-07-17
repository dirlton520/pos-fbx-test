<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_manage_delivery_statuses_permission extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210329213905_manage_delivery_statuses_permission.sql'));
	    }

	    public function down() 
			{
	    }

	}