<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_count_cash_bills extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190722095015_count_cash_bills.sql'));
	    }

	    public function down() 
			{
	    }

	}