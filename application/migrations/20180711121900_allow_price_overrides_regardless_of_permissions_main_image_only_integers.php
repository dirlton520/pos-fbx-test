<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_allow_price_overrides_regardless_of_permissions_main_image_only_integers extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180711121900_allow_price_overrides_regardless_of_permissions_main_image_only_integers.sql'));
	    }

	    public function down() 
			{
	    }

	}