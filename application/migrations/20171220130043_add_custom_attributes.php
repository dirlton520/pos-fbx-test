<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_add_custom_attributes extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20171220130043_add_custom_attributes.sql'));
	    }

	    public function down() 
			{
	    }

	}