<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_price_rules_per_location extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20181227130745_price_rules_per_location.sql'));
	    }

	    public function down() 
			{
	    }

	}