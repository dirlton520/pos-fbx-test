<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_manager_override_price_adjustments extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200909122154_manager_override_price_adjustments.sql'));
	    }

	    public function down() 
			{
	    }

	}