<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_non_taxable_on_sales_table extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20201228105828_non_taxable_on_sales_table.sql'));
	    }

	    public function down() 
			{
	    }

	}