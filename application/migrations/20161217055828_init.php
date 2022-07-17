<?php
class Migration_init extends MY_Migration 
{
  public function up() 
	{
		$tables_in_db = $this->db->list_tables();
		//Fill up database for initial load
		if (count($tables_in_db) == 0 || (count($tables_in_db) == 1 && $tables_in_db[0] == $this->db->dbprefix('migrations')))
		{
				$this->execute_sql(realpath(dirname(__FILE__).'/../../database/database.sql'));
		}
		else
		{	
			$migration_start_index = $this->get_migration_start_index();
			$migration_order = array(
				'database_10.0-10.1.sql',
				'database_10.1-10.2.sql',
				'database_10.3-10.4.sql',
				'database_10.4-10.5.sql',
				'database_10.5-10.6.sql',
				'database_10.9-11.0.sql',
				'database_11.0-11.1.sql',
				'database_11.1-11.2.sql',
				'database_11.2-11.3.sql',
				'database_11.6-12.0.sql',
				'database_12.1-12.2.sql',
				'database_12.3-12.4.sql',
				'database_12.4-12.5.sql',
				'database_12.5-12.6.sql',
				'database_12.9-12.10.sql',
				'database_12.11-12.12.sql',
				'database_12.12-12.13.sql',
				'database_12.13-12.14.sql',
				'database_12.14-12.15.sql',
				'database_12.17-12.18.sql',
				'database_12.18-12.19.sql',
				'database_12.19-12.20.sql',
				'database_12.20-12.21.sql',
				'database_12.22-13.0.sql',
				'database_13.0-13.1.sql',
				'database_13.1-13.2.sql',
				'database_13.2-14.0.sql',
				'database_14.0-14.1.sql',
				'database_14.1-14.2.sql',
				'database_14.2-14.3.sql',
				'database_14.3-14.4.sql',
				'database_14.4-15.0.sql',
			);
			
			if (isset($migration_start_index) && $migration_start_index !== FALSE)
			{
				for($k=$migration_start_index;$k<count($migration_order);$k++)
				{
						$this->execute_sql(dirname(__FILE__).'/init/'.$migration_order[$k]);
				}
			}
		}
  }
	
	public function get_migration_start_index()
	{
		$this->load->model('Appconfig');
		$version = $this->Appconfig->get_raw_version_value();
		
		//if 15.0 we can exit right away
		if ($version == '15.0')
		{
			return FALSE;
		}
		
		if ($version == '13.1')
		{
			$migration_start_index = 25;
		}
		elseif($version == '13.2')
		{
			$migration_start_index = 26;
		}
		elseif($version == '14.0')
		{
			$migration_start_index = 27;
		}
		elseif($version == '14.1')
		{
			$migration_start_index = 28;
		}
		elseif($version == '14.2')
		{
			$migration_start_index = 29;
		}
		elseif($version == '14.3')
		{
			$migration_start_index = 30;
		}
		elseif($version == '14.4')
		{
			$migration_start_index = 31;
		}
		elseif($this->is_legacy_version('10.0'))
		{
			$migration_start_index = 0;
		}
		elseif ($this->is_legacy_version('10.1'))
		{
			$migration_start_index = 1;
		}
		elseif ($this->is_legacy_version('10.3'))
		{
			$migration_start_index = 2;
		}
		elseif ($this->is_legacy_version('10.4'))
		{
			$migration_start_index = 3;
		}
		elseif ($this->is_legacy_version('10.5'))
		{
			$migration_start_index = 4;
		}
		elseif ($this->is_legacy_version('10.9'))
		{
			$migration_start_index = 5;
		}
		elseif ($this->is_legacy_version('11.0'))
		{
			$migration_start_index = 6;
		}
		elseif ($this->is_legacy_version('11.1'))
		{
			$migration_start_index = 7;
		}
		elseif ($this->is_legacy_version('11.2'))
		{
			$migration_start_index = 8;
		}
		elseif ($this->is_legacy_version('11.6'))
		{
			$migration_start_index = 9;
		}
		elseif ($this->is_legacy_version('12.1'))
		{
			$migration_start_index = 10;
		}
		elseif ($this->is_legacy_version('12.3'))
		{
			$migration_start_index = 11;
		}
		elseif ($this->is_legacy_version('12.4'))
		{
			$migration_start_index = 12;
		}
		elseif ($this->is_legacy_version('12.5'))
		{
			$migration_start_index = 13;
		}
		elseif ($this->is_legacy_version('12.9'))
		{
			$migration_start_index = 14;
		}
		elseif ($this->is_legacy_version('12.11'))
		{
			$migration_start_index = 15;
		}
		elseif ($this->is_legacy_version('12.12'))
		{
			$migration_start_index = 16;
		}
		elseif ($this->is_legacy_version('12.13'))
		{
			$migration_start_index = 17;
		}
		elseif ($this->is_legacy_version('12.14'))
		{
			$migration_start_index = 18;
		}
		elseif ($this->is_legacy_version('12.17'))
		{
			$migration_start_index = 19;
		}
		elseif ($this->is_legacy_version('12.18'))
		{
			$migration_start_index = 20;
		}
		elseif ($this->is_legacy_version('12.19'))
		{
			$migration_start_index = 21;
		}
		elseif ($this->is_legacy_version('12.20'))
		{
			$migration_start_index = 22;
		}
		elseif ($this->is_legacy_version('12.22'))
		{
			$migration_start_index = 23;
		}
		elseif ($this->is_legacy_version('13.0'))
		{
			$migration_start_index = 24;
		}
		if (isset($migration_start_index))
		{
			return $migration_start_index;
		}
		
		return FALSE;
	}
	
	function is_legacy_version($version)
	{
		if($version == '10.0')
		{
			return !$this->db->field_exists('taxable','customers');
		}
		elseif($version == '10.1')
		{
			return !$this->db->table_exists('sales_payments');
		}
		elseif($version == '10.3')
		{
			return !$this->db->field_exists('company_name','suppliers');
		}
		elseif($version == '10.4')
		{
			return !$this->db->field_exists('line','sales_items');
		}
		elseif($version == '10.5')
		{
			return !$this->db->table_exists('inventory');
		}
		elseif($version == '10.9')
		{
			return !$this->db->table_exists('giftcards');
		}
		elseif($version == '11.0')
		{
			$db_name = $this->db->database;
			$items_table = $this->db->dbprefix('items');
			$constraint_name = $this->db->dbprefix('sales_items_taxes_ibfk_2');
			$query = "SELECT * FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = '$db_name' AND REFERENCED_TABLE_NAME = '$items_table' AND CONSTRAINT_NAME = '$constraint_name'";
			$rows = $this->db->query($query)->num_rows();
			//Doesn't reference items table; this means we are 11.0
			return $rows == 0;
		}
		elseif($version == '11.1')
		{
			//This means we already past 11.1 where we dropped the suspended sales table; so we can return false
			if ($this->db->field_exists('suspended','sales'))
			{
				return FALSE;
			}
			return !$this->db->table_exists('sales_suspended');
		}
		elseif($version == '11.2')
		{
			return !$this->db->table_exists('item_kits');
		}
		elseif ($version == '11.6')
		{
			return !$this->db->field_exists('deleted','sales');
		}
		elseif ($version == '12.1')
		{
			$db_name = $this->db->database;
			$app_config_table = $this->db->dbprefix('app_config');
			$query = "SELECT CCSA.character_set_name FROM information_schema.`TABLES` T,information_schema.`COLLATION_CHARACTER_SET_APPLICABILITY` CCSA WHERE CCSA.collation_name = T.table_collation AND T.table_schema = '$db_name' AND T.table_name = '$app_config_table'";
			$row = $this->db->query($query)->row();
			//If you are NOT utf8_unicode_ci then we are 12.1
			return $row->character_set_name != 'utf8';
		}
		elseif ($version == '12.3')
		{
			return !$this->db->field_exists('deleted','receivings');
		}
		elseif ($version == '12.4')
		{
			return !$this->db->table_exists('item_kits_taxes');
		}
		elseif ($version == '12.5')
		{
			return !$this->db->field_exists('cumulative','items_taxes');
		}
		elseif ($version == '12.9')
		{
			return !$this->db->field_exists('item_kit_number','item_kits');
		}
		elseif ($version == '12.11')
		{
			return !$this->db->field_exists('company_name','customers');
		}
		elseif ($version == '12.12')
		{
			return !$this->db->table_exists('app_files');		
		}
		elseif ($version == '12.13')
		{
			if ($this->db->table_exists('sessions'))
			{
				$field_data = $this->db->field_data('sessions');
				$user_agent_field = false;
				foreach($field_data as $field)
				{
					if ($field->name == 'user_agent')
					{
						return $field->max_length !=120;
					}
				}
			}
			
			return false;		
		}
		elseif ($version == '12.14')
		{
			return !$this->db->table_exists('register_log');		
		}
		elseif ($version == '12.17')
		{
			return !$this->db->field_exists('suspended','sales');
		}
		elseif ($version == '12.18')
		{
			return !$this->db->field_exists('promo_price','items');
		}
		elseif ($version == '12.19')
		{
				return !$this->db->table_exists('modules_actions');		
		}
		elseif ($version == '12.20')
		{
			$field_data = $this->db->field_data('modules_actions');
			$user_agent_field = false;
			foreach($field_data as $field)
			{
				if ($field->name == 'action_id')
				{
					return $field->max_length !=100;
				}
			}
			
			return false;
		}
		elseif ($version == '12.22')
		{
			return !$this->db->field_exists('show_comment_on_receipt','sales');
		}			
		elseif ($version == '13.0')
		{
			return !$this->db->field_exists('cc_token','customers');
		}
		return false;
	}
	
  public function down() 
  {
  
  }
}