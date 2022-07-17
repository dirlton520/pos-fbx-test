<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_serial_numbers extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/20161227195844_serial_numbers.sql'));
	    }

	    public function down() 
			{
	    }

	}