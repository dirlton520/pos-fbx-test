<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_process_returns extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190208113205_process_returns.sql'));
	    }

	    public function down() 
			{
	    }

	}