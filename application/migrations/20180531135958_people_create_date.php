<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_people_create_date extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180531135958_people_create_date.sql'));
	    }

	    public function down() 
			{
	    }

	}