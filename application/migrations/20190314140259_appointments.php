<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_appointments extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190314140259_appointments.sql'));
	    }

	    public function down() 
			{
	    }

	}