<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_can_suspend_sale_permission extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190211161013_can_suspend_sale_permission.sql'));
	    }

	    public function down() 
			{
	    }

	}