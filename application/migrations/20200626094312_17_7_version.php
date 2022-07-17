<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_17_7_version extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200626094312_17_7_version.sql'));
	    }

	    public function down() 
			{
	    }

	}