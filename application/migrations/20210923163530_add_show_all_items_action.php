<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_add_show_all_items_action extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210923163530_add_show_all_items_action.sql'));
	    }

	    public function down() 
			{
	    }

	}