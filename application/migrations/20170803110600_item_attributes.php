<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_item_attributes extends MY_Migration 
	{

	    public function up() 
			{
				$this->execute_sql(realpath(dirname(__FILE__).'/'.'20170803110600_item_attributes.sql'));
				
				$item_lookup_order = unserialize($this->config->item('item_lookup_order'));
				$item_lookup_order[] = 'item_variation_item_number';
				$this->Appconfig->save('item_lookup_order',serialize($item_lookup_order));
	    }

	    public function down() 
			{
	    }

	}