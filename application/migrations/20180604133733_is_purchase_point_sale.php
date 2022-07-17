<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_is_purchase_point_sale extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180604133733_is_purchase_point_sale.sql'));
	    }

	    public function down() 
			{
	    }

	}