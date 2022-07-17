<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_phppos_bitrix_item_variations extends MY_Migration 
	{

	    public function up() 
		{
			$this->execute_sql(realpath(dirname(__FILE__).'/'.'20220411153450_phppos_bitrix_item_variations.sql'));
	    }

	    public function down() 
		{
	    }

	} 
