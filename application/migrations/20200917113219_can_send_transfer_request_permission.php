<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_can_send_transfer_request_permission extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200917113219_can_send_transfer_request_permission.sql'));
	    }

	    public function down() 
			{
	    }

	}