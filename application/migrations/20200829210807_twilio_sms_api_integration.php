<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_twilio_sms_api_integration extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200829210807_twilio_sms_api_integration.sql'));
	    }

	    public function down() 
			{
	    }

	}