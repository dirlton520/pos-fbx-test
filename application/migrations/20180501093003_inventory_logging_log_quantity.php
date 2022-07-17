<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_inventory_logging_log_quantity extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180501093003_inventory_logging_log_quantity.sql'));
	    }

	    public function down() 
			{
	    }

	}