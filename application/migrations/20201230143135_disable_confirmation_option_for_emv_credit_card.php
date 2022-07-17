<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_disable_confirmation_option_for_emv_credit_card extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20201230143135_disable_confirmation_option_for_emv_credit_card.sql'));
	    }

	    public function down() 
			{
	    }

	}