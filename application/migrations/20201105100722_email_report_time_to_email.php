<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_email_report_time_to_email extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20201105100722_email_report_time_to_email.sql'));
	    }

	    public function down() 
			{
	    }

	}