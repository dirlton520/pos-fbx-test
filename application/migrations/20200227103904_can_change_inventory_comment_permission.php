<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_can_change_inventory_comment_permission extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200227103904_can_change_inventory_comment_permission.sql'));
	    }

	    public function down() 
			{
	    }

	}