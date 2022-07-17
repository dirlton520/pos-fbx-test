<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_quickbooks_online extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20181109130256_quickbooks_online.sql'));
	    }

	    public function down() 
			{
	    }

	}