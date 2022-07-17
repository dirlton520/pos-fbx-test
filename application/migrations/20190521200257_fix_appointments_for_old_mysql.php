<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_fix_appointments_for_old_mysql extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190521200257_fix_appointments_for_old_mysql.sql'));
	    }

	    public function down() 
			{
	    }

	}