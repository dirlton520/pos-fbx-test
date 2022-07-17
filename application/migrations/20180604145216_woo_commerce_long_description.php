<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_woo_commerce_long_description extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180604145216_woo_commerce_long_description.sql'));
	    }

	    public function down() 
			{
	    }

	}