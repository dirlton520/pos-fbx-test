<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_tax_id_locations extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200130085207_tax_id_locations.sql'));
	    }

	    public function down() 
			{
	    }

	}