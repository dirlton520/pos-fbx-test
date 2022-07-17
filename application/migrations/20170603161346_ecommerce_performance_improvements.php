<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_ecommerce_performance_improvements extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170603161346_ecommerce_performance_improvements.sql'));
				$ecommerce_cron_sync_operations = unserialize($this->config->item('ecommerce_cron_sync_operations'));
				
				//If we are importing items we should also import categories and tags
				if (in_array('import_ecommerce_items_into_phppos',$ecommerce_cron_sync_operations))
				{
					array_unshift($ecommerce_cron_sync_operations, 'import_ecommerce_categories_into_phppos');
					array_unshift($ecommerce_cron_sync_operations, 'import_ecommerce_tags_into_phppos');
					$this->Appconfig->save('ecommerce_cron_sync_operations',serialize($ecommerce_cron_sync_operations));
				}
	    }

	    public function down() 
			{
	    }

	}