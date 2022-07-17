<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_cc_and_bcc_email extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20171009130828_cc_and_bcc_email.sql'));
	    }

	    public function down() 
			{
	    }

	}