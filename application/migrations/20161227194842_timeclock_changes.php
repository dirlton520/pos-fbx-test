<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_Timeclock_changes extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/20161227194842_timeclock_changes.sql'));
	    }

	    public function down() 
			{
	    }

	}