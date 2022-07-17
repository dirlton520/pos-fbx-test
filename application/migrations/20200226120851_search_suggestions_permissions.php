<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_search_suggestions_permissions extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200226120851_search_suggestions_permissions.sql'));
	    }

	    public function down() 
			{
	    }

	}