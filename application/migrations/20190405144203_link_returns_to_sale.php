<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_link_returns_to_sale extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190405144203_link_returns_to_sale.sql'));
	    }

	    public function down() 
			{
	    }

	}