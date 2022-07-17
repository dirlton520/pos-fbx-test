<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_price_rules_reports extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180607105206_price_rules_reports.sql'));
	    }

	    public function down() 
			{
	    }

	}