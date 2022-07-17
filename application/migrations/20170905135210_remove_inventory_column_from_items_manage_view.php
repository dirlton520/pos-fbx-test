<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	class Migration_remove_inventory_column_from_items_manage_view extends MY_Migration 
	{

	    public function up() 
			{
				$this->load->model('Employee_appconfig');
				
				$item_column_prefs = $this->Employee_appconfig->get_key_for_all_employees('item_column_prefs');
				
				foreach($item_column_prefs as $row)
				{
					$data = unserialize($row['value']);
					$index_of_inventory = array_search('inventory' ,$data);
					if ($index_of_inventory!== FALSE)
					{
						unset($data[$index_of_inventory]);
						$data = array_values($data); //Rekeys the array to not have a hole
						$this->Employee_appconfig->save('item_column_prefs',serialize($data),$row['employee_id']);
					}
				}
			}

	    public function down() 
			{
	    }

	}