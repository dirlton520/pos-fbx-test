<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_rename_legacy_search_method extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170525133341_rename_legacy_search_method.sql'));
	    }

	    public function down() 
			{
	    }

	}