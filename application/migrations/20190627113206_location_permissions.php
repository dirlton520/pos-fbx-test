<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_location_permissions extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190627113206_location_permissions.sql'));
	    }

	    public function down() 
			{
	    }

	}