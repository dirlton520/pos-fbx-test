<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_item_and_item_kit_info_popup extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20191204115548_item_and_item_kit_info_popup.sql'));
	    }

	    public function down() 
			{
	    }

	}