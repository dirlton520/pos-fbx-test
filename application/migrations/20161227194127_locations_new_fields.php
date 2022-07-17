<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_locations_new_fields extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/20161227194127_locations_new_fields.sql'));
	    }

	    public function down() 
			{
	    }

	}