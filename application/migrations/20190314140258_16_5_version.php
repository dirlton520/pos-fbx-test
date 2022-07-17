<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_16_5_version extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190314140258_16_5_version.sql'));
	    }

	    public function down() 
			{
	    }

	}