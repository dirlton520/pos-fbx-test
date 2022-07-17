<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_damaged_items_outside_of_returns extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20191010113028_damaged_items_outside_of_returns.sql'));
	    }

	    public function down() 
			{
	    }

	}