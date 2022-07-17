<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_allow_ecommerce_order_id_to_be_larger_int extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210125161506_allow_ecommerce_order_id_to_be_larger_int.sql'));
	    }

	    public function down() 
			{
	    }

	}