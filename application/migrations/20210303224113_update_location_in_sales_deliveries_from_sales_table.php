<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_update_location_in_sales_deliveries_from_sales_table extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210303224113_update_location_in_sales_deliveries_from_sales_table.sql'));
	    }

	    public function down() 
			{
	    }

	}