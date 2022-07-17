<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_multiple_currency extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170620121857_multiple_currency.sql'));
	    }

	    public function down() 
			{
	    }

	}