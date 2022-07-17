<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_added_internal_notes_to_supplier_table extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20211027002146_added_internal_notes_to_supplier_table.sql'));
	    }

	    public function down() 
			{
	    }

	}