<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_shopify extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20201228113741_shopify.sql'));
	    }

	    public function down() 
			{
	    }

	}