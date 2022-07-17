<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_add_expenses_files_table extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210824202540_add_expenses_files_table.sql'));
	    }

	    public function down() 
			{
	    }

	}