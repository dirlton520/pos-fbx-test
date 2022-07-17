<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_appointments_can_be_any_person extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190513144006_appointments_can_be_any_person.sql'));
	    }

	    public function down() 
			{
	    }

	}