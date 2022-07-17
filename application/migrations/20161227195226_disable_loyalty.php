<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_disable_loyalty extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/20161227195226_disable_loyalty.sql'));
	    }

	    public function down() 
			{
	    }

	}