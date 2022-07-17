<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_recalculate_receiving_item_totals extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170829205521_recalculate_receiving_item_totals.sql'));
	    }

	    public function down() 
			{
	    }

	}