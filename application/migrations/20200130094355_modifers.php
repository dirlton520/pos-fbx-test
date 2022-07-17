<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_modifers extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200130094355_modifers.sql'));
	    }

	    public function down() 
			{
	    }

	}