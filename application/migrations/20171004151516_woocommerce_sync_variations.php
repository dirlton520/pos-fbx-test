<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_woocommerce_sync_variations extends MY_Migration 
	{

	    public function up() 
			{
				$this->load->model('Appconfig');
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20171004151516_woocommerce_sync_variations.sql'));
				$ecommerce_cron_sync_operations = unserialize($this->Appconfig->get_key_directly_from_database('ecommerce_cron_sync_operations'));
				
				//If we are importing items we should also import/export variations
				if (in_array('import_ecommerce_items_into_phppos',$ecommerce_cron_sync_operations))
				{					
					array_unshift($ecommerce_cron_sync_operations, 'import_ecommerce_attributes_into_phppos');
					array_push($ecommerce_cron_sync_operations, 'export_phppos_attributes_to_ecommerce');
					$this->Appconfig->save('ecommerce_cron_sync_operations',serialize($ecommerce_cron_sync_operations));
				}
				
	    }

	    public function down() 
			{
	    }

	}