<?php
class Appconfig extends MY_Model 
{
	
	function exists($key)
	{
		$this->db->from('app_config');	
		$this->db->where('app_config.key',$key);
		$query = $this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	function get_all()
	{
		$this->db->from('app_config');
		$this->db->order_by("key", "asc");
		return $this->db->get();		
	}
	
	function get($key)
	{
		return $this->config->item($key);
	}
	
	function delete($key)
	{
		if ($key)
		{
			$this->db->where('key',$key);
			$this->db->delete('app_config');
		}
	}
	function save($key,$value)
	{
		$config_data = array(
			'key'=>$key,
			'value'=>$value
		);
		return $this->db->replace('app_config', $config_data);
	}
	
	function get_key_directly_from_database($key)
	{
		$this->db->from('app_config');
		$this->db->where("key", $key);
		$row = $this->db->get()->row_array();
		if (!empty($row))
		{
			return $row['value'];
		}
		return NULL;	
	}
	
	function get_raw_kill_ecommerce_cron()
	{
		$this->db->from('app_config');
		$this->db->where("key", "kill_ecommerce_cron");
		$row = $this->db->get()->row_array();
		if (!empty($row))
		{
			return $row['value'];
		}
		return 0;	
	}
	
	function get_raw_qb_cron_running()
	{
		$this->db->from('app_config');
		$this->db->where("key", "qb_cron_running");
		$row = $this->db->get()->row_array();
		if (!empty($row))
		{
			return $row['value'];
		}
		return 0;	
	}
	
	function get_raw_kill_qb_cron()
	{
		$this->db->from('app_config');
		$this->db->where("key", "kill_qb_cron");
		$row = $this->db->get()->row_array();
		if (!empty($row))
		{
			return $row['value'];
		}
		return 0;	
	}
	
	function get_raw_ecommerce_cron_running()
	{
		$this->db->from('app_config');
		$this->db->where("key", "ecommerce_cron_running");
		$row = $this->db->get()->row_array();
		if (!empty($row))
		{
			return $row['value'];
		}
		return 0;	
	}
	
	function get_raw_number_of_decimals()
	{
		$this->db->from('app_config');
		$this->db->where("key", "number_of_decimals");
		$row = $this->db->get()->row_array();
		if (!empty($row))
		{
			return $row['value'];
		}
		return 2;	
	}
	
	function get_raw_language_value()
	{
		$this->db->from('app_config');
		$this->db->where("key", "language");
		$row = $this->db->get()->row_array();
		if (!empty($row))
		{
			return $row['value'];
		}
		return '';	
	}

	function get_raw_version_value()
	{
		$this->db->from('app_config');
		$this->db->where("key", "version");
		$row = $this->db->get()->row_array();
		if (!empty($row))
		{
			return $row['value'];
		}
		return '';	
	}
		
	function get_force_https()
	{
		if ($this->db->table_exists('app_config'))
		{
			$this->db->from('app_config');
			$this->db->where("key", "force_https");
			$row = $this->db->get()->row_array();
			if (!empty($row))
			{
				return $row['value'];
			}
			return '';
		}
		
		return '';
	}
	
	function get_do_not_force_http()
	{
     $this->db->from('app_config');
     $this->db->where("key", "do_not_force_http");
     $row = $this->db->get()->row_array();
     if (!empty($row))
     {
			 return $row['value'];
     }
     return '';
	}
	
	function get_raw_phppos_session_expiration()
	{
		$this->db->from('app_config');
		$this->db->where("key", "phppos_session_expiration");
		$row = $this->db->get()->row_array();
		if (!empty($row))
		{
			if (is_numeric($row['value']))
			{
				return (int)$row['value'];
			}
			
		}
		return NULL;	
	}
	
	function batch_save($data)
	{
		if (isset($data['default_tax_1_name']))
		{
			//Check for duplicate taxes
			for($k = 1;$k<=5;$k++)
			{
				$current_tax = $data["default_tax_${k}_name"].$data["default_tax_${k}_rate"];
			
				for ($j = 1;$j<=5;$j++)
				{
					$check_tax = $data["default_tax_${j}_name"].$data["default_tax_${j}_rate"];
					if ($j!=$k && $current_tax != '' && $check_tax != '')
					{
						if ($current_tax == $check_tax)
						{
							return FALSE;
						}
					}
				}
			}
		}
		
		$success = true;
		
		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();
		foreach($data as $key=>$value)
		{
			if(!$this->save($key, $value))
			{
				$success=false;
				break;
			}
		}
		
		$this->db->trans_complete();		
		return $success;
		
	}
		
	function get_logo_image()
	{
		if ($this->config->item('company_logo'))
		{
			return secure_app_file_url($this->get('company_logo'));
		}
		return  base_url().'assets/img/header_logo.png';
	}
		
	function get_additional_payment_types()
	{
		$return = array();
		$payment_types = $this->get('additional_payment_types');
		
		if ($payment_types)
		{
			$return = array_map('trim', explode(',',$payment_types));
		}
		
		return $return;
	}
	
	function mark_mercury_activate($mercury_activate_seen = true)
	{
		$this->db->query('REPLACE INTO '.$this->db->dbprefix('app_config').' (`key`, `value`) VALUES ("mercury_activate_seen", "'.($mercury_activate_seen ? 1 : 0).'")');
	}
	
	function mark_reseller_message($reseller_activate_seen = true)
	{
		$this->db->query('REPLACE INTO '.$this->db->dbprefix('app_config').' (`key`, `value`) VALUES ("reseller_activate_seen", "'.($reseller_activate_seen ? 1 : 0).'")');
	}
	
	function mark_bluejay_message($bluejay_seen = true)
	{
		$this->db->query('REPLACE INTO '.$this->db->dbprefix('app_config').' (`key`, `value`) VALUES ("bluejay_seen", "'.($bluejay_seen ? 1 : 0).'")');
	}
	
	function set_all_locations_use_global_tax()
	{
		$this->load->model('Location');
		return $this->Location->set_all_locations_use_global_tax();
	}
	
	function all_locations_use_global_tax()
	{
		$this->load->model('Location');
		return $this->Location->all_locations_use_global_tax();
	}
	
	function get_primary_key_next_index($table)
	{
		$tables_to_col = array(
			'items' => 'item_id',
			'item_kits'=> 'item_kit_id',
			'sales' => 'sale_id',
			'receivings' => 'receiving_id',	
		);
		
		if(isset($tables_to_col[$table]))
		{
			$this->db->select("IFNULL(MAX(".$tables_to_col[$table]."),0)+1 as max_id", false);
			$this->db->from($table);
			$max_id = $this->db->get()->row()->max_id;
			
			return $max_id;
		}
		
		return false;
	}
	
	function change_auto_increment($table, $value)
	{	
		if(!is_numeric($value) || intval($value) < 1)
		{
			return false;
		}
		
		$max = intVal($this->get_primary_key_next_index($table));
			
		if(intval($value) < $max)
		{
			$value = $max +1;
		}
			
		$this->db->query('ALTER TABLE '. $this->db->dbprefix($table). ' AUTO_INCREMENT '. $value);
		
		return $value;
	}
	
	function get_exchange_rates()
	{
		$this->db->from('currency_exchange_rates');
		$this->db->order_by('id');
		return $this->db->get();
	}
		
	
	
	
	function save_exchange_rates($currency_exchange_rates_to, $currency_exchange_rates_symbol, $currency_exchange_rates_rate,$currency_exchange_rates_symbol_location,$currency_exchange_rates_number_of_decimals,$currency_exchange_rates_thousands_separator,$currency_exchange_rates_decimal_point)
	{
		$this->db->truncate('currency_exchange_rates');
		$currency_exchange_rates_to = $currency_exchange_rates_to ? $currency_exchange_rates_to : array();
		for($k = 0; $k< count($currency_exchange_rates_to); $k++)
		{
			$currency_exchange_rate_to = $currency_exchange_rates_to[$k];
			$currency_exchange_rate_symbol = $currency_exchange_rates_symbol[$k];
			$currency_exchange_rate = $currency_exchange_rates_rate[$k];			
			$currency_exchange_rate_symbol_location = $currency_exchange_rates_symbol_location[$k];
			$currency_exchange_rate_number_of_decimals = $currency_exchange_rates_number_of_decimals[$k];
			$currency_exchange_rate_thousands_separator = $currency_exchange_rates_thousands_separator[$k];
			$currency_exchange_rate_decimal_point = $currency_exchange_rates_decimal_point[$k];
				
			$this->db->insert('currency_exchange_rates', array(
				'currency_symbol' => $currency_exchange_rate_symbol,
				'currency_code_to' => $currency_exchange_rate_to,
				'exchange_rate' => $currency_exchange_rate,
				'currency_symbol_location' => $currency_exchange_rate_symbol_location,
				'number_of_decimals' => $currency_exchange_rate_number_of_decimals,
				'thousands_separator' => $currency_exchange_rate_thousands_separator,
				'decimal_point' => $currency_exchange_rate_decimal_point,
			));
		}
		
		return true;
	}
	
	public function get_api_keys()
	{
		$this->db->from('keys');
		$this->db->order_by('id');
		return $this->db->get()->result();
	}

	function get_qb_classes()
	{
		$this->db->from('app_config');
		$this->db->where("key", "qb_classes");
		$row = $this->db->get()->row_array();
		if (!empty($row))
		{
			return $row['value'];
		}
		return 0;	
	}

	function get_qb_journal_entry_records()
	{
		$this->db->from('app_config');
		$this->db->where("key", "qb_journal_entry_records");
		$row = $this->db->get()->row_array();
		if (!empty($row))
		{
			return $row['value'];
		}
		return 0;	
	}

	
	
  public function generate_key()
  {
    do
    {
        // Generate a random salt
        $salt = base_convert(bin2hex($this->security->get_random_bytes(64)), 16, 36);

        // If an error occurred, then fall back to the previous method
        if ($salt === FALSE)
        {
            $salt = hash('sha256', time() . mt_rand());
        }

        $new_key = substr($salt, 0, config_item('rest_key_length'));
    }
    while ($this->key_exists($new_key));

    return $new_key;
  }
	
  /* Private Data Methods */


  private function key_exists($key)
  {
      return $this->db
          ->where(config_item('rest_key_column'), $key)
          ->count_all_results(config_item('rest_keys_table')) > 0;
  }

  public function insert_key($key, $data)
  {
      $data[config_item('rest_key_column')] = sha1($key);
			$data['key_ending'] = substr($key,-7);
      $data['date_created'] = function_exists('now') ? now() : time();

      return $this->db
          ->set($data)
          ->insert(config_item('rest_keys_table'));
  }
	public function delete_api_key($api_key_id)
	{
  	$this->db->where('id', $api_key_id)->delete(config_item('rest_keys_table'));
	}
	
	public function get_barcoded_labels()
	{
		$this->db->from('app_config');
		$this->db->order_by("key", "asc");
		$this->db->like('key','barcoded_labels_','after');
		return $this->db->get();		
	}
	
	function get_ecommerce_locations()
	{
		$return = array();
		
		$this->db->from('ecommerce_locations');
		$rows = $this->db->get()->result_array();
		
		foreach($rows as $row)
		{
			$return[$row['location_id']] = TRUE;
		}
		
		if (empty($return))
		{
			$return[1] = TRUE;			
		}
		
		return $return;
	}
	
	function save_ecommerce_locations($locations)
	{
		$this->db->truncate('ecommerce_locations');
		
		if (is_array($locations))
		{
			foreach($locations as $location_id)
			{
				$this->db->insert('ecommerce_locations',array('location_id' => $location_id));
			}
		}
		else
		{
			$this->db->insert('ecommerce_locations',array('location_id' => 1));
		}
	}
	function get_damaged_reasons_options()
	{
		$damaged_reason_options = array();
		$damaged_reason_options[''] = lang('common_none');
		$reasons = explode(',',$this->config->item('damaged_reasons'));
		
		if ($reasons[0] != '')
		{
			foreach($reasons as $reason)
			{
				$damaged_reason_options[$reason] = $reason;
			}
		}
		return $damaged_reason_options;
	}
	
	function get_secure_key()
	{
		if ($this->exists('phppos_secure_key'))
		{
			return $this->get('phppos_secure_key');
		}
		
		if (function_exists('openssl_random_pseudo_bytes'))
		{
			$secure_key = bin2hex(openssl_random_pseudo_bytes(16));
		}
		else
		{
			$secure_key = md5(rand());
		}
		
		$this->save('phppos_secure_key',$secure_key);	
		return $secure_key;
	}


	function get_replaceable_keywords(){
		return array(
			"company_name"			=> "{{company_name}}",
			"company_website"		=> "{{company_website}}",
			"location_name"			=> "{{location_name}}",
			"location_address"		=> "{{location_address}}",
			"location_company"		=> "{{location_company}}",
			"location_website"		=> "{{location_website}}",
			"location_phone"		=> "{{location_phone}}",
			"location_fax"			=> "{{location_fax}}",
			"location_email"		=> "{{location_email}}",
			"person_first_name"		=> "{{person_first_name}}",
			"person_last_name"		=> "{{person_last_name}}",
			"person_full_name"		=> "{{person_full_name}}",
			"person_phone"			=> "{{person_phone}}",
			"person_email"			=> "{{person_email}}",
			"person_address_1"		=> "{{person_address_1}}",
			"person_address_2"		=> "{{person_address_2}}",
			"person_city"			=> "{{person_city}}",
			"person_state"			=> "{{person_state}}",
			"person_zip"			=> "{{person_zip}}",
			"person_country"		=> "{{person_country}}",
			"employee_first_name"	=> "{{employee_first_name}}",
			"employee_last_name"	=> "{{employee_last_name}}",
			"employee_full_name"	=> "{{employee_full_name}}",
			"employee_phone"		=> "{{employee_phone}}",
			"employee_email"		=> "{{employee_email}}",
			"employee_address_1"	=> "{{employee_address_1}}",
			"employee_address_2"	=> "{{employee_address_2}}",
			"employee_city"			=> "{{employee_city}}",
			"employee_state"		=> "{{employee_state}}",
			"employee_zip"			=> "{{employee_zip}}",
			"employee_country"		=> "{{employee_country}}",
		);
	}

	function replace_keywords_with_actual_word($text, $location_id=null, $person_id=null, $employee_id=null){
		extract($this->get_replaceable_keywords());

		$text = str_replace(
			array($company_name, $company_website), 
			array($this->config->item('company'), $this->config->item('website')), 
			$text
		);

		if($location_id){
			$location = $this->Location->get_info($location_id);
			$text = str_replace(
				array($location_name, $location_address, $location_company, $location_website, $location_phone, $location_fax, $location_email), 
				array($location->name, $location->address, $location->company, $location->website, $location->phone, $location->fax, $location->email), 
				$text
			);
		}

		if($person_id){
			$person = $this->Person->get_info($person_id);
			$text = str_replace(
				array($person_first_name, $person_last_name, $person_full_name, $person_phone, $person_email, $person_address_1, $person_address_2, $person_city, $person_state, $person_zip, $person_country), 
				array($person->first_name, $person->last_name, $person->full_name, $person->phone_number, $person->email, $person->address_1, $person->address_2, $person->city, $person->state, $person->zip, $person->country), 
				$text
			);
		}

		if($employee_id){
			$employee = $this->employee->get_info($employee_id);
			$text = str_replace(
				array($employee_first_name, $employee_last_name, $employee_full_name, $employee_phone, $employee_email, $employee_address_1, $employee_address_2, $employee_city, $employee_state, $employee_zip, $employee_country), 
				array($employee->first_name, $employee->last_name, $employee->full_name, $employee->phone_number, $employee->email, $employee->address_1, $employee->address_2, $employee->city, $employee->state, $employee->zip, $employee->country), 
				$text
			);
		}

		return $text;
	}
	
}	

?>