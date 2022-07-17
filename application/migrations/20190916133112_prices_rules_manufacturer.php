<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_prices_rules_manufacturer extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190916133112_prices_rules_manufacturer.sql'));
	    }

	    public function down() 
			{
	    }

	}