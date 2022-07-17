<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_price_rules_mix_and_match extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190612133418_price_rules_mix_and_match.sql'));
	    }

	    public function down() 
			{
	    }

	}