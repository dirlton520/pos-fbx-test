<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_stock_alerts_just_for_reorder_level extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190401155906_stock_alerts_just_for_reorder_level.sql'));
	    }

	    public function down() 
			{
	    }

	}