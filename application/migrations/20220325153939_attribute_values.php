<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_attribute_values extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20220325153939_attribute_values.sql'));
	    }

	    public function down() 
			{
	    }

	}