<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_phppos_bitrix_field_mapping extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/20220405163215_phppos_bitrix_field_mapping.sql'));
	    }

	    public function down() 
			{
	    }

	}