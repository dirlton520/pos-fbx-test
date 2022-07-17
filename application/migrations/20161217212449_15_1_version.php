<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_15_1_version extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20161217212449_15_1_version.sql'));
	    }

	    public function down() 
			{
	    }

	}