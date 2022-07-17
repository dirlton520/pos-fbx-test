<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_sales_received_quantity extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190711155237_sales_received_quantity.sql'));
	    }

	    public function down() 
			{
	    }

	}