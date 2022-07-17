<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_coupon_codes extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170329095203_coupon_codes.sql'));
	    }

	    public function down() 
			{
	    }

	}