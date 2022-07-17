<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_import_ecommerce_orders_suspended_type extends MY_Migration 
	{

	    public function up() 
		{
			$this->load->model('Sale_types');
			$sale_type_data = array('name' => lang('common_ecommerce'), 'sort' => 99,'remove_quantity' => 1,'system_sale_type' => 0);
			$this->Sale_types->save($sale_type_data,-1);
			$this->Appconfig->save('ecommerce_suspended_sale_type_id',$sale_type_data['id']);
			
	    }

	    public function down() 
		{
	    }
	}