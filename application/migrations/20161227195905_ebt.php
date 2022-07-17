<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_ebt extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/20161227195905_ebt.sql'));
	    }

	    public function down() 
			{
	    }

	}