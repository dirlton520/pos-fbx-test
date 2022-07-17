<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_disable_markup_markdown_per_location extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200323141245_disable_markup_markdown_per_location.sql'));
	    }

	    public function down() 
			{
	    }

	}