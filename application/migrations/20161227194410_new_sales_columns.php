<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_new_sales_columns extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/20161227194410_new_sales_columns.sql'));
	    }

	    public function down() 
			{
	    }

	}