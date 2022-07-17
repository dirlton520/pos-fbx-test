<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_timeclock_enhancements extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180801140202_timeclock_enhancements.sql'));
	    }

	    public function down() 
			{
	    }

	}