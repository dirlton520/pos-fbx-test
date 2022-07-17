<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_can_change_sale_date_permission extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200917121620_can_change_sale_date_permission.sql'));
	    }

	    public function down() 
			{
	    }

	}