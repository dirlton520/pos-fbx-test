<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_woocommerce_sync_tax_classes extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20180501094421_woocommerce_sync_tax_classes.sql'));
				
				require_once (APPPATH."models/interfaces/Ecom.php");
				$ecom_model = Ecom::get_ecom_model();
				if (!$ecom_model)
				{
					$this->load->model('Appconfig');
					
					//import tax classes
					$ecommerce_cron_sync_operations = unserialize($this->Appconfig->get_key_directly_from_database('ecommerce_cron_sync_operations'));
					$index = array_search('import_ecommerce_attributes_into_phppos',$ecommerce_cron_sync_operations);
					
					if ($index===FALSE)
					{
						$ecommerce_cron_sync_operations[] = 'import_tax_classes_into_phppos';
					}
					else
					{
						array_splice($ecommerce_cron_sync_operations, $index+1, 0, 'import_tax_classes_into_phppos');
					}
					
					$this->Appconfig->save('ecommerce_cron_sync_operations',serialize($ecommerce_cron_sync_operations));
					
					
					//export tax classes
					$ecommerce_cron_sync_operations = unserialize($this->Appconfig->get_key_directly_from_database('ecommerce_cron_sync_operations'));
					$index = array_search('export_phppos_attributes_to_ecommerce',$ecommerce_cron_sync_operations);
					
					if ($index===FALSE)
					{
						$ecommerce_cron_sync_operations[] = 'export_tax_classes_into_phppos';
					}
					else
					{
						array_splice($ecommerce_cron_sync_operations, $index+1, 0, 'export_tax_classes_into_phppos');
					}
					
					$this->Appconfig->save('ecommerce_cron_sync_operations',serialize($ecommerce_cron_sync_operations));
				}
	    }

	    public function down() 
			{
	    }

	}