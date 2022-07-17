<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_weight_units extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210107101523_weight_units.sql'));
	    }

	    public function down() 
			{
	    }

	}