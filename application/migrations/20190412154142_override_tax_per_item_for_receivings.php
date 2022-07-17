<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_override_tax_per_item_for_receivings extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190412154142_override_tax_per_item_for_receivings.sql'));
	    }

	    public function down() 
			{
	    }

	}