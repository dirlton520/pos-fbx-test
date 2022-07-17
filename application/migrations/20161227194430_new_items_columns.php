<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_new_items_columns extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/20161227194430_new_items_columns.sql'));
	    }

	    public function down() 
			{
	    }

	}