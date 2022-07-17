<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_allow_decimal_quantity_for_variations extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20211119183534_allow_decimal_quantity_for_variations.sql'));
	    }

	    public function down() 
			{
	    }

	}