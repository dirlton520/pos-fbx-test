<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_item_number_quantity_unit extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200521135727_item_number_quantity_unit.sql'));
	    }

	    public function down() 
			{
	    }

	}