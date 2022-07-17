<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_custom_fields_for_sales extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180820143411_custom_fields_for_sales.sql'));
	    }

	    public function down() 
			{
	    }

	}