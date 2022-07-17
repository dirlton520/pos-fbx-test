<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_larger_invoice_item_description extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170406093837_larger_invoice_item_description.sql'));
	    }

	    public function down() 
			{
	    }

	}