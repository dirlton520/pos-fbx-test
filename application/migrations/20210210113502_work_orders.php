<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_work_orders extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210210113502_work_orders.sql'));
	    }

	    public function down() 
			{
	    }

	}