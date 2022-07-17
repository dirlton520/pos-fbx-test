<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_store_account_changes_and_suppliers extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/20161227194932_store_account_changes_and_suppliers.sql'));
	    }

	    public function down() 
			{
	    }

	}