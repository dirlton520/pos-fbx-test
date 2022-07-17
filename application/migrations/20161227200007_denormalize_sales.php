<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_denormalize_sales extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/20161227200007_denormalize_sales.sql'));
	    }

	    public function down() 
			{
	    }

	}