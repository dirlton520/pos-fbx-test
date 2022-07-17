<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_store_config_show_company_barcode extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170908113410_store_config_show_company_barcode.sql'));
	    }

	    public function down() 
			{
	    }

	}