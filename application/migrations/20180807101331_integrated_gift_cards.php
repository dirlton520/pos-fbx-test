<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_integrated_gift_cards extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180807101331_integrated_gift_cards.sql'));
	    }

	    public function down() 
			{
	    }

	}