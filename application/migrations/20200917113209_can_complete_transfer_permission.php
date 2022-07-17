<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_can_complete_transfer_permission extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200917113209_can_complete_transfer_permission.sql'));
	    }

	    public function down() 
			{
	    }

	}