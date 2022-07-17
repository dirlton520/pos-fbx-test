<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_register_reports extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170905123856_register_reports.sql'));
	    }

	    public function down() 
			{
	    }

	}