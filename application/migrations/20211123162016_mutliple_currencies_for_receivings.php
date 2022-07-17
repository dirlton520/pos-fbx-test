<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_mutliple_currencies_for_receivings extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20211123162016_mutliple_currencies_for_receivings.sql'));
	    }

	    public function down() 
			{
	    }

	}