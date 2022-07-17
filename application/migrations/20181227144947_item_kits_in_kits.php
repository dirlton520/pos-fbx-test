<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_item_kits_in_kits extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20181227144947_item_kits_in_kits.sql'));
	    }

	    public function down() 
			{
	    }

	}