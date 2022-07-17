<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_add_delivery_type_column_to_salse_deliveries extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210216083224_add_delivery_type_column_to_salse_deliveries.sql'));
	    }

	    public function down() 
			{
	    }

	}