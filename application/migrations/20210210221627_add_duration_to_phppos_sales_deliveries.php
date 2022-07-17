<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_add_duration_to_phppos_sales_deliveries extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210210221627_add_duration_to_phppos_sales_deliveries.sql'));
	    }

	    public function down() 
			{
	    }

	}