<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_item_lookup_order extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170626110831_item_lookup_order.sql'));
	    }

	    public function down() 
			{
	    }

	}