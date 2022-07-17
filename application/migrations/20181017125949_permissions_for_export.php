<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_permissions_for_export extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20181017125949_permissions_for_export.sql'));
	    }

	    public function down() 
			{
	    }

	}