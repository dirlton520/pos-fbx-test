<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_serial_number_variations extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210409131830_serial_number_variations.sql'));
	    }

	    public function down() 
			{
	    }

	}