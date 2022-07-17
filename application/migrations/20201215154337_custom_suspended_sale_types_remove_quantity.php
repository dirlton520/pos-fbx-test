<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_custom_suspended_sale_types_remove_quantity extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20201215154337_custom_suspended_sale_types_remove_quantity.sql'));
	    }

	    public function down() 
			{
	    }

	}