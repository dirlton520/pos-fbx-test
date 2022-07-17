<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_tips extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190507114612_tips.sql'));
	    }

	    public function down() 
			{
	    }

	}