<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_additional_item_numbers_change extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/20161227194056_additional_item_numbers_change.sql'));
	    }

	    public function down() 
			{
	    }

	}