<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_price_tiers_cost_plus_fixed_amount extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170526150214_price_tiers_cost_plus_fixed_amount.sql'));
	    }

	    public function down() 
			{
	    }

	}