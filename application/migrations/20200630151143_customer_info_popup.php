<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_customer_info_popup extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200630151143_customer_info_popup.sql'));
	    }

	    public function down() 
			{
	    }

	}