<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_new_import extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170105124104_new_import.sql'));
	    }

	    public function down() 
			{
	    }

	}