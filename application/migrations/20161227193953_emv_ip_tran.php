<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_emv_ip_tran extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/20161227193953_emv_ip_tran.sql'));
	    }

	    public function down() 
			{
	    }

	}