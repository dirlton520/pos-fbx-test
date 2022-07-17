<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_damaged_goods_sales_return extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190319113909_damaged_goods_sales_return.sql'));
	    }

	    public function down() 
			{
	    }

	}