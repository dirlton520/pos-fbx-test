<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_custom_fields_for_receivings extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180822120622_custom_fields_for_receivings.sql'));
	    }

	    public function down() 
			{
	    }

	}