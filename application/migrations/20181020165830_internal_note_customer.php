<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_internal_note_customer extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20181020165830_internal_note_customer.sql'));
	    }

	    public function down() 
			{
	    }

	}