<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_attach_files_to_people extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180611152521_attach_files_to_people.sql'));
	    }

	    public function down() 
			{
	    }

	}