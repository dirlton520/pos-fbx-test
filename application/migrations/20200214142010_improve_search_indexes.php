<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_improve_search_indexes extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200214142010_improve_search_indexes.sql'));
	    }

	    public function down() 
			{
	    }

	}