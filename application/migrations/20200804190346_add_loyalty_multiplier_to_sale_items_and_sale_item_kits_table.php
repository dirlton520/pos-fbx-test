<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_add_loyalty_multiplier_to_sale_items_and_sale_item_kits_table extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200804190346_add_loyalty_multiplier_to_sale_items_and_sale_item_kits_table.sql'));
	    }

	    public function down() 
			{
	    }

	}