<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_view_inventory_at_all_locations_items extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20191119133109_view_inventory_at_all_locations_items.sql'));
	    }

	    public function down() 
			{
	    }

	}