<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_card_connect extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200325103345_card_connect.sql'));
	    }

	    public function down() 
			{
	    }

	}