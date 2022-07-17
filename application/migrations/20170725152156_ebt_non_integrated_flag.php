<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_ebt_non_integrated_flag extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170725152156_ebt_non_integrated_flag.sql'));
	    }

	    public function down() 
			{
	    }

	}