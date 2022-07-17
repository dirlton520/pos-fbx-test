<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_deliveries extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170426121304_deliveries.sql'));
	    }

	    public function down() 
			{
	    }

	}