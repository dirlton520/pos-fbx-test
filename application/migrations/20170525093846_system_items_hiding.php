<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_system_items_hiding extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170525093846_system_items_hiding.sql'));
	    }

	    public function down() 
			{
	    }

	}