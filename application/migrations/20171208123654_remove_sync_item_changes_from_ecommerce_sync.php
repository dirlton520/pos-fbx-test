<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_remove_sync_item_changes_from_ecommerce_sync extends MY_Migration 
	{

	    public function up() 
			{
				$this->load->model('Appconfig');
				
				$ecommerce_cron_sync_operations = unserialize($this->Appconfig->get_key_directly_from_database('ecommerce_cron_sync_operations'));
				if (($index = array_search('sync_phppos_item_changes',$ecommerce_cron_sync_operations)) !== FALSE)
				{
					unset($ecommerce_cron_sync_operations[$index]);
					$ecommerce_cron_sync_operations = array_values($ecommerce_cron_sync_operations);
				}
				$this->Appconfig->save('ecommerce_cron_sync_operations',serialize($ecommerce_cron_sync_operations));
	    }

	    public function down() 
			{
	    }

	}