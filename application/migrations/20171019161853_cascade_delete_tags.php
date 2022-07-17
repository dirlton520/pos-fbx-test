<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_cascade_delete_tags extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20171019161853_cascade_delete_tags.sql'));
	    }

	    public function down() 
			{
	    }

	}