<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_location_banning_tags extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20211122145208_location_banning_tags.sql'));
	    }

	    public function down() 
			{
	    }

	}