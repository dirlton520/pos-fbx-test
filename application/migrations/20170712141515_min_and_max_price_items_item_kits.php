<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_min_and_max_price_items_item_kits extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170712141515_min_and_max_price_items_item_kits.sql'));
	    }

	    public function down() 
			{
	    }

	}