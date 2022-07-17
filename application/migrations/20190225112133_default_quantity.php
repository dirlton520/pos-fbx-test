<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_default_quantity extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190225112133_default_quantity.sql'));
	    }

	    public function down() 
			{
	    }

	}