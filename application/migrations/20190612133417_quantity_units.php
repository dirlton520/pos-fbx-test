<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_quantity_units extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190612133417_quantity_units.sql'));
	    }

	    public function down() 
			{
	    }

	}