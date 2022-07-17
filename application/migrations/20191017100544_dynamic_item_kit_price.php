<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_dynamic_item_kit_price extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20191017100544_dynamic_item_kit_price.sql'));
	    }

	    public function down() 
			{
	    }

	}