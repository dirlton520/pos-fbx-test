<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_added_location_id_to_sales_deliveries_table extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210210221632_added_location_id_to_sales_deliveries_table.sql'));
	    }

	    public function down() 
			{
	    }

	}