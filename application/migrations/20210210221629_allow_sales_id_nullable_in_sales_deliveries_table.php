<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_allow_sales_id_nullable_in_sales_deliveries_table extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210210221629_allow_sales_id_nullable_in_sales_deliveries_table.sql'));
	    }

	    public function down() 
			{
	    }

	}