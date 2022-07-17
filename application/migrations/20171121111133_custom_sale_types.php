<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_custom_sale_types extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20171121111133_custom_sale_types.sql'));
	    }

	    public function down() 
			{
	    }

	}