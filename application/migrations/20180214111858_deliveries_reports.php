<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_deliveries_reports extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180214111858_deliveries_reports.sql'));
	    }

	    public function down() 
			{
	    }

	}