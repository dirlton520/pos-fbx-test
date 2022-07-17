<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_promo_price_no_date_needed extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20171102130646_promo_price_no_date_needed.sql'));
	    }

	    public function down() 
			{
	    }

	}