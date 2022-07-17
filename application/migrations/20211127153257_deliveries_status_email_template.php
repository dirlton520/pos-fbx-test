<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_deliveries_status_email_template extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20211127153257_deliveries_status_email_template.sql'));
	    }

	    public function down() 
			{
	    }

	}