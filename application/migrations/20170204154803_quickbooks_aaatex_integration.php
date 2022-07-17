<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_quickbooks_aaatex_integration extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170204154803_quickbooks_aaatex_integration.sql'));
	    }

	    public function down() 
			{
	    }

	}