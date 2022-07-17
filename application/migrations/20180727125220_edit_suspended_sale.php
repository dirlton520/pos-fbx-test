<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_edit_suspended_sale extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180727125220_edit_suspended_sale.sql'));
	    }

	    public function down() 
			{
	    }

	}