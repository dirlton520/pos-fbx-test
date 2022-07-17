<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_replenish_level_per_location extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170831155419_replenish_level_per_location.sql'));
	    }

	    public function down() 
			{
	    }

	}