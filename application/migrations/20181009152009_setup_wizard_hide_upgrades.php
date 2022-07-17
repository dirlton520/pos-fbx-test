<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_setup_wizard_hide_upgrades extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20181009152009_setup_wizard_hide_upgrades.sql'));
	    }

	    public function down() 
			{
	    }

	}