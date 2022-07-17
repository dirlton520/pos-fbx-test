<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_age_verify extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180322154232_age_verify.sql'));
	    }

	    public function down() 
			{
	    }

	}