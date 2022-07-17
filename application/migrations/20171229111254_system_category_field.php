<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_system_category_field extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20171229111254_system_category_field.sql'));
				
				$fee = get_all_language_values_for_key('common_fee','common');
				$refund = get_all_language_values_for_key('common_refund','common');
				$delivery_fee = get_all_language_values_for_key('common_delivery_fee','common');
				$store_account_payment = get_all_language_values_for_key('common_store_account_payment','common');
				$discount = get_all_language_values_for_key('common_discount','common');
				$giftcard = get_all_language_values_for_key('common_giftcard','common');
				
				$system_category_names = array_merge($fee,$refund,$delivery_fee,$store_account_payment,$discount,$giftcard);
				
				$this->db->set('system_category', 1);
				$this->db->where_in('name', $system_category_names);
				$this->db->update('categories');
	    }

	    public function down() 
			{
	    }

	}