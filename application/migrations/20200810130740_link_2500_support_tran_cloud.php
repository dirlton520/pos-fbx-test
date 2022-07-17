<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_link_2500_support_tran_cloud extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200810130740_link_2500_support_tran_cloud.sql'));
	    }

	    public function down() 
			{
	    }

	}