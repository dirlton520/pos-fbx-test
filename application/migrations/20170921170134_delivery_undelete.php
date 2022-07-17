<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_delivery_undelete extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170921170134_delivery_undelete.sql'));
	    }

	    public function down() 
			{
	    }

	}