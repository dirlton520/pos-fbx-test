<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_pricing_history extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190625132157_pricing_history.sql'));
	    }

	    public function down() 
			{
	    }

	}