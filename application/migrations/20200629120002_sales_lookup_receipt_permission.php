<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_sales_lookup_receipt_permission extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200629120002_sales_lookup_receipt_permission.sql'));
	    }

	    public function down() 
			{
	    }

	}