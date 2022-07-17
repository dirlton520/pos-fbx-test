<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_disable_loyalty_per_price_rule extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200317123435_disable_loyalty_per_price_rule.sql'));
	    }

	    public function down() 
			{
	    }

	}