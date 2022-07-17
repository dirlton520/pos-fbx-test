<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_damaged_reason extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200420102441_damaged_reason.sql'));
	    }

	    public function down() 
			{
	    }

	}