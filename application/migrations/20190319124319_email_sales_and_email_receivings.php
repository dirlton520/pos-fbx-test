<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_email_sales_and_email_receivings extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20190319124319_email_sales_and_email_receivings.sql'));
	    }

	    public function down() 
			{
	    }

	}