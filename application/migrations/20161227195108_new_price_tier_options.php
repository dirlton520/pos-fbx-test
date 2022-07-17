<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_new_price_tier_options extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/20161227195108_new_price_tier_options.sql'));
	    }

	    public function down() 
			{
	    }

	}