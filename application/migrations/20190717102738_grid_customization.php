<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_grid_customization extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190717102738_grid_customization.sql'));
	    }

	    public function down() 
			{
	    }

	}