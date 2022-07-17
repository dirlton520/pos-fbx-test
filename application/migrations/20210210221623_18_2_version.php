<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_18_2_version extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210210221623_18_2_version.sql'));
	    }

	    public function down() 
			{
	    }

	}