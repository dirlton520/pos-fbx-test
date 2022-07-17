<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_secondary_categories extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20201228113740_secondary_categories.sql'));
	    }

	    public function down() 
			{
	    }

	}