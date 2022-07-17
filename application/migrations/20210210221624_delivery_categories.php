<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_delivery_categories extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210210221624_delivery_categories.sql'));
	    }

	    public function down() 
			{
	    }

	}