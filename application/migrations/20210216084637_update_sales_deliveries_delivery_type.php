<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_update_sales_deliveries_delivery_type extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210216084637_update_sales_deliveries_delivery_type.sql'));
	    }

	    public function down() 
			{
	    }

	}