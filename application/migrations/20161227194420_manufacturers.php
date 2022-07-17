<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_manufacturers extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/20161227194420_manufacturers.sql'));
	    }

	    public function down() 
			{
	    }

	}