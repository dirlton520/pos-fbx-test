<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_permission_for_delete_suspended_receivings extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210706175912_permission_for_delete_suspended_receivings.sql'));
	    }

	    public function down() 
			{
	    }

	}