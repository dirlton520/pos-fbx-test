<?php
/**
 * @author   Natan Felles <natanfelles@gmail.com>
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Migration_create_table_api_keys
 *
 * @property CI_DB_forge         $dbforge
 * @property CI_DB_query_builder $db
 */
class Migration_create_table_api_keys extends MY_Migration {


	public function up()
	{
		$table = config_item('rest_keys_table');
		$fields = array(
			'id'                           => array(
				'type'           => 'INT(11)',
				'auto_increment' => TRUE,
				'unsigned'       => TRUE,
			),
			'user_id'                      => array(
				'type'     => 'INT(11)',
				'unsigned' => TRUE,
				'null' => TRUE,
			),
			config_item('rest_key_column') => array(
				'type'   => 'VARCHAR(' . config_item('rest_key_length') . ')',
				'unique' => TRUE,
			),
			'key_ending'    => array(
				'type'   => 'VARCHAR(' . config_item('rest_key_length') . ')',
			),
			'description'    => array(
				'type' => 'TEXT',
			),
			'level'                        => array(
				'type' => 'INT(2)',
			),
			'ignore_limits'                => array(
				'type'    => 'TINYINT(1)',
				'default' => 0,
			),
			'is_private_key'               => array(
				'type'    => 'TINYINT(1)',
				'default' => 0,
			),
			'ip_addresses'                 => array(
				'type' => 'TEXT',
				'null' => TRUE,
			),
			'date_created'                 => array(
				'type' => 'INT(11)',
			),
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table($table,FALSE,array('ENGINE' => 'InnoDB'));
		$table = $this->db->dbprefix($table);
		$this->db->query(add_foreign_key($table, 'user_id', $this->db->dbprefix('employees').'(person_id)', 'CASCADE', 'CASCADE'));
	}


	public function down()
	{
	}

}
