<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_delivery_item_kits extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210210221631_delivery_item_kits.sql'));
	    }

	    public function down() 
			{
	    }

	}