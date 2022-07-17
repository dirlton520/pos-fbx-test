<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_migrate_categories_for_expenses extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210102122557_migrate_categories_for_expenses.sql'));
	    }

	    public function down() 
			{
	    }

	}