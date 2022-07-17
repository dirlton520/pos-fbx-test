<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_add_receipt_line_sort_order_field extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20211101182945_add_receipt_line_sort_order_field.sql'));
	    }

	    public function down() 
			{
	    }

	}