<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_location_variation_prices extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190411085910_location_variation_prices.sql'));
	    }

	    public function down() 
			{
	    }

	}