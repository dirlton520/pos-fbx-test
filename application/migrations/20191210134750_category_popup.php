<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_category_popup extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20191210134750_category_popup.sql'));
	    }

	    public function down() 
			{
	    }

	}