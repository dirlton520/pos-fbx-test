<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_update_foreign_key_for_expenses_categories extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210104214650_update_foreign_key_for_expenses_categories.sql'));
	    }

	    public function down() 
			{
	    }

	}