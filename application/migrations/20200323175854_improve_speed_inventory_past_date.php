<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_improve_speed_inventory_past_date extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200323175854_improve_speed_inventory_past_date.sql'));
	    }

	    public function down() 
			{
	    }

	}