<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_add_always_sms_receipt_field_to_customers extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20201012095537_add_always_sms_receipt_field_to_customers.sql'));
	    }

	    public function down() 
			{
	    }

	}