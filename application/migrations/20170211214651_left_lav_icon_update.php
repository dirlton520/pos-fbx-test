<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_left_lav_icon_update extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170211214651_left_lav_icon_update.sql'));
	    }

	    public function down() 
			{
	    }

	}