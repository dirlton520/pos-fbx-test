<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_inventory_past_date extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190322133135_inventory_past_date.sql'));
	    }

	    public function down() 
			{
	    }

	}