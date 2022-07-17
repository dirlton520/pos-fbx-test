<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_current_or_previous_day_option_for_auto_email extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20201230095246_current_or_previous_day_option_for_auto_email.sql'));
	    }

	    public function down() 
			{
	    }

	}