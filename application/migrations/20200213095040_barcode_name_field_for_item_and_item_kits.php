<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_barcode_name_field_for_item_and_item_kits extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200213095040_barcode_name_field_for_item_and_item_kits.sql'));
	    }

	    public function down() 
			{
	    }

	}