<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_category_exclude_from_e_commerce extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190703122122_category_exclude_from_e_commerce.sql'));
	    }

	    public function down() 
			{
	    }

	}