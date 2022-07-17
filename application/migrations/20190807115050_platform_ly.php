<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_platform_ly extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190807115050_platform_ly.sql'));
	    }

	    public function down() 
			{
	    }

	}