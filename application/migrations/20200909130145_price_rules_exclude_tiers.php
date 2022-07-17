<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_price_rules_exclude_tiers extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200909130145_price_rules_exclude_tiers.sql'));
	    }

	    public function down() 
			{
	    }

	}