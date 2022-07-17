<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_exclude_item_from_price_rules extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190327142835_exclude_item_from_price_rules.sql'));
	    }

	    public function down() 
			{
	    }

	}