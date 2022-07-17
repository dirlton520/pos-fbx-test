<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_company_id_supplier extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20220330142839_company_id_supplier.sql'));
	    }

	    public function down() 
			{
	    }

	} 
