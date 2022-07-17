<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_series_package extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180814134808_series_package.sql'));
	    }

	    public function down() 
			{
	    }

	}