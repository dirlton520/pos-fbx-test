<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_serialnumber_index extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20201104110252_serialnumber_index.sql'));
	    }

	    public function down() 
			{
	    }

	}