<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_expiring_files_index extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200206102752_expiring_files_index.sql'));
	    }

	    public function down() 
			{
	    }

	}