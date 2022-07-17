<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_location_ban_item extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20210920155838_location_ban_item.sql'));
	    }

	    public function down() 
			{
	    }

	}