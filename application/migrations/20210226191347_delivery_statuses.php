<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_delivery_statuses extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210226191347_delivery_statuses.sql'));
	    }

	    public function down() 
			{
	    }

	}