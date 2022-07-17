<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_16_2_version extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180423133959_16_2_version.sql'));
	    }

	    public function down() 
			{
	    }

	}