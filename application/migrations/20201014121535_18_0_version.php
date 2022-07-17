<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_18_0_version extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20201014121535_18_0_version.sql'));
	    }

	    public function down() 
			{
	    }

	}