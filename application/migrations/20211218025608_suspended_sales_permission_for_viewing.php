<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_suspended_sales_permission_for_viewing extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20211218025608_suspended_sales_permission_for_viewing.sql'));
	    }

	    public function down() 
			{
	    }

	}