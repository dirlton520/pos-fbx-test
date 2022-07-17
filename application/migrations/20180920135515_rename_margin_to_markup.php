<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_rename_margin_to_markup extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180920135515_rename_margin_to_markup.sql'));
	    }

	    public function down() 
			{
	    }

	}