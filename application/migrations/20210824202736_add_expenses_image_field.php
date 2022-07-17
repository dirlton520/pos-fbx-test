<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_add_expenses_image_field extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210824202736_add_expenses_image_field.sql'));
	    }

	    public function down() 
			{
	    }

	}