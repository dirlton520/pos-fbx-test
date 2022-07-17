<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_damaged_reason_comment_field extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20201127105123_damaged_reason_comment_field.sql'));
	    }

	    public function down() 
			{
	    }

	}