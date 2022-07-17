<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_can_lookup_last_receipt_permission extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210610145726_can_lookup_last_receipt_permission.sql'));
	    }

	    public function down() 
			{
	    }

	}