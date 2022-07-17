<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_expenses_categories extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210102122556_expenses_categories.sql'));
	    }

	    public function down() 
			{
	    }

	}