<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_sessions_table_change extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170308112334_sessions_table_change.sql'));
	    }

	    public function down() 
			{
	    }

	}