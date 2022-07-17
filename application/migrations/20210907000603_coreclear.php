<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_coreclear extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210907000603_coreclear.sql'));
	    }

	    public function down() 
			{
	    }

	}