<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_denormalize_recv extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/20161227200026_denormalize_recv.sql'));
	    }

	    public function down() 
			{
	    }

	}