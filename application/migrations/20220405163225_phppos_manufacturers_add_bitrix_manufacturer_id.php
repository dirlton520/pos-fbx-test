<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_phppos_manufacturers_add_bitrix_manufacturer_id extends MY_Migration 
	{

	    public function up() 
		{
			$this->execute_sql(realpath(dirname(__FILE__).'/20220405163225_phppos_manufacturers_add_bitrix_manufacturer_id.sql'));
	    }

	    public function down() 
		{
	    }

	}