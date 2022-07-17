<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_index_on_filename extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210127162834_index_on_filename.sql'));
	    }

	    public function down() 
			{
	    }

	}