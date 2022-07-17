<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_remove_fulltext_from_most_entities extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/20161227200238_remove_fulltext_from_most_entities.sql'));
	    }

	    public function down() 
			{
	    }

	}