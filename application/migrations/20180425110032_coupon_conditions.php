<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_coupon_conditions extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180425110032_coupon_conditions.sql'));
	    }

	    public function down() 
			{
	    }

	}