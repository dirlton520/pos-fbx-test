<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_15_2_version extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170327133305_15_2_version.sql'));
	    }

	    public function down() 
			{
	    }

	}