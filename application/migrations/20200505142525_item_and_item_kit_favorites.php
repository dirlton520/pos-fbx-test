<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_item_and_item_kit_favorites extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200505142525_item_and_item_kit_favorites.sql'));
	    }

	    public function down() 
			{
	    }

	}