<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_limited_discounts_for_items_and_item_kits extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170710101951_limited_discounts_for_items_and_item_kits.sql'));
	    }

	    public function down() 
			{
	    }

	}