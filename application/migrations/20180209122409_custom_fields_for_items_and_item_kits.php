<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_custom_fields_for_items_and_item_kits extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180209122409_custom_fields_for_items_and_item_kits.sql'));
	    }

	    public function down() 
			{
	    }

	}