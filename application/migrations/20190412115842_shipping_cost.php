<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_shipping_cost extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190412115842_shipping_cost.sql'));
	    }

	    public function down() 
			{
	    }

	}