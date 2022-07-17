<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_non_barcoded_items extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180904134904_non_barcoded_items.sql'));
	    }

	    public function down() 
			{
	    }

	}