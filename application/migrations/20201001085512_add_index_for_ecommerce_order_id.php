<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_add_index_for_ecommerce_order_id extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20201001085512_add_index_for_ecommerce_order_id.sql'));
	    }

	    public function down() 
			{
	    }

	}