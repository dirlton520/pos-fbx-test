<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_delivery_items extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210210221630_delivery_items.sql'));
	    }

	    public function down() 
			{
	    }

	}