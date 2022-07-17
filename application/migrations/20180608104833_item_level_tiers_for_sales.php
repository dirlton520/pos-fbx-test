<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_item_level_tiers_for_sales extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180608104833_item_level_tiers_for_sales.sql'));
	    }

	    public function down() 
			{
	    }

	}