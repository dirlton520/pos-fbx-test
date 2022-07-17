<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_attributes extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20220325143139_attributes.sql'));
	    }

	    public function down() 
			{
	    }

	}