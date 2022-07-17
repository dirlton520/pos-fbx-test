<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_supplier_custom_fields extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170515100952_supplier_custom_fields.sql'));
	    }

	    public function down() 
			{
	    }

	}