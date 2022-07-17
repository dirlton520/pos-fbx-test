<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_square extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20181010211108_square.sql'));
	    }

	    public function down() 
			{
	    }

	}