<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_price_rules extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/20161217212459_price_rules.sql'));
	    }

	    public function down() 
			{
	    }

	}