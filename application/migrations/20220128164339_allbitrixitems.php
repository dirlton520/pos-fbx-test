<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_AllBitrixItems extends MY_Migration 
	{
	    public function up() 
		{
			$this->execute_sql(realpath(dirname(__FILE__).'/'.'20220128164339_allbitrixitems.sql'));
	    }
	    public function down() 
		{
	    }

	}