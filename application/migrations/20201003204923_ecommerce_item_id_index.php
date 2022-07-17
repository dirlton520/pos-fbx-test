<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_ecommerce_item_id_index extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20201003204923_ecommerce_item_id_index.sql'));
	    }

	    public function down() 
			{
	    }

	}