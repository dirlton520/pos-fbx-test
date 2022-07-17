<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_serial_number_cost_price extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20181109130255_serial_number_cost_price.sql'));
	    }

	    public function down() 
			{
	    }

	}