<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_is_ecommerce_for_variations extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180517143352_is_ecommerce_for_variations.sql'));
	    }

	    public function down() 
			{
	    }

	}