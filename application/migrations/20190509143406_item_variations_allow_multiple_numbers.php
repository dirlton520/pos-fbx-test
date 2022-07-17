<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_item_variations_allow_multiple_numbers extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190509143406_item_variations_allow_multiple_numbers.sql'));
	    }

	    public function down() 
			{
	    }

	}