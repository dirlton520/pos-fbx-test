<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_change_price_rules_icon extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170327133309_change_price_rules_icon.sql'));
	    }

	    public function down() 
			{
	    }

	}