<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_delivery_files_added extends MY_Migration 
	{

	    public function up()
		{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20211121073454_delivery_files_added.sql'));
	    }

	    public function down() 
		{
			
	    }

	}