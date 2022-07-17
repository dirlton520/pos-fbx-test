<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_add_ecommerce_last_modified extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20171219134338_add_ecommerce_last_modified.sql'));
	    }

	    public function down() 
			{
	    }

	}