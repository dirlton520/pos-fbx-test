<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_fulltext_variation extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20171227122125_fulltext_variation.sql'));
	    }

	    public function down() 
			{
	    }

	}