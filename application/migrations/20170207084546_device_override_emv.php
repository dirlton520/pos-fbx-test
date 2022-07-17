<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_device_override_emv extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170207084546_device_override_emv.sql'));
	    }

	    public function down() 
			{
	    }

	}