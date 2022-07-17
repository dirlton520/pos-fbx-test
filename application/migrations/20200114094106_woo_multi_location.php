<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_woo_multi_location extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20200114094106_woo_multi_location.sql'));
				$ecom_store_location = $this->config->item('ecom_store_location') ? $this->config->item('ecom_store_location') : 1;				
				$this->db->insert('ecommerce_locations',array('location_id' => $ecom_store_location));
				
	    }

	    public function down() 
			{
	    }

	}