<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_mailchimp_default_mailing_lists extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200810130741_mailchimp_default_mailing_lists.sql'));
	    }

	    public function down() 
			{
	    }

	}