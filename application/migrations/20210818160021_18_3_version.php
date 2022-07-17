<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_18_3_version extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210818160021_18_3_version.sql'));
	    }

	    public function down() 
			{
	    }

	}