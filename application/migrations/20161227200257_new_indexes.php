<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_new_indexes extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/20161227200257_new_indexes.sql'));
	    }

	    public function down() 
			{
	    }

	}