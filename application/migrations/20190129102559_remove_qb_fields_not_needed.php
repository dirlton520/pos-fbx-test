<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_remove_qb_fields_not_needed extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190129102559_remove_qb_fields_not_needed.sql'));
	    }

	    public function down() 
			{
	    }

	}