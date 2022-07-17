<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_weight_and_dimensions extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180516142322_weight_and_dimensions.sql'));
	    }

	    public function down() 
			{
	    }

	}