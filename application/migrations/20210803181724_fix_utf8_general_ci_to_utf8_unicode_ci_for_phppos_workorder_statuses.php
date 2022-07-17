<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_fix_utf8_general_ci_to_utf8_unicode_ci_for_phppos_workorder_statuses extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210803181724_fix_utf8_general_ci_to_utf8_unicode_ci_for_phppos_workorder_statuses.sql'));
	    }

	    public function down() 
			{
	    }

	}