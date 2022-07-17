<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_permission_for_inventory_print_list extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20191008121747_permission_for_inventory_print_list.sql'));
	    }

	    public function down() 
			{
	    }

	}