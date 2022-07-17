<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_last_edited_columns extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190522144137_last_edited_columns.sql'));
	    }

	    public function down() 
			{
	    }

	}