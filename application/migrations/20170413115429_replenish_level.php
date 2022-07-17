<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_replenish_level extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170413115429_replenish_level.sql'));
	    }

	    public function down() 
			{
	    }

	}