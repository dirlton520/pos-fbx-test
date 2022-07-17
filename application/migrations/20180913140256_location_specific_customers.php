<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_location_specific_customers extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180913140256_location_specific_customers.sql'));
	    }

	    public function down() 
			{
	    }

	}