<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_woocommerce_import_shipping_classes extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180516142323_woocommerce_import_shipping_classes.sql'));
				
				require_once (APPPATH."models/interfaces/Ecom.php");
				$ecom_model = Ecom::get_ecom_model();
				if (!$ecom_model)
				{
					$this->load->model('Appconfig');
					
					//import shipping classes
					$ecommerce_cron_sync_operations = unserialize($this->Appconfig->get_key_directly_from_database('ecommerce_cron_sync_operations'));
					$index = array_search('import_tax_classes_into_phppos',$ecommerce_cron_sync_operations);
					
					if ($index===FALSE)
					{
						$ecommerce_cron_sync_operations[] = 'import_shipping_classes_into_phppos';
					}
					else
					{
						array_splice($ecommerce_cron_sync_operations, $index+1, 0, 'import_shipping_classes_into_phppos');
					}
					
					$this->Appconfig->save('ecommerce_cron_sync_operations',serialize($ecommerce_cron_sync_operations));
				}
	    }

	    public function down() 
			{
	    }

	}