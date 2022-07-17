<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_larger_fields extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/20161227194348_larger_fields.sql'));
	    }

	    public function down() 
			{
	    }

	}