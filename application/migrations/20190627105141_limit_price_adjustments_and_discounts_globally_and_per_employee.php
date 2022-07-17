<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_limit_price_adjustments_and_discounts_globally_and_per_employee extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190627105141_limit_price_adjustments_and_discounts_globally_and_per_employee.sql'));
	    }

	    public function down() 
			{
	    }

	}