<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_new_items_are_ecommerce_by_default extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180213095340_new_items_are_ecommerce_by_default.sql'));
	    }

	    public function down() 
			{
	    }

	}