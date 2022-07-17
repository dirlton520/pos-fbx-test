<?php
/**
 * @author   Natan Felles <natanfelles@gmail.com>
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Migration_create_table_api_access
 *
 * @property CI_DB_forge         $dbforge
 * @property CI_DB_query_builder $db
 */
class Migration_create_table_api_access extends MY_Migration {


	public function up()
	{
		$table = config_item('rest_access_table');
		$fields = array(
			'id'            => array(
				'type'           => 'INT(11)',
				'auto_increment' => TRUE,
				'unsigned'       => TRUE,
			),
			'key'           => array(
				'type' => 'VARCHAR(' . config_item('rest_key_length') . ')',
			),
			'all_access'    => array(
				'type'    => 'TINYINT(1)',
				'default' => 0,
			),
			'controller'    => array(
				'type' => 'VARCHAR(50)',
			),
			'date_created'  => array(
				'type' => 'DATETIME',
				'null' => TRUE,
			),
			'date_modified' => array(
				'type' => 'TIMESTAMP',
			),
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->add_key('controller');
		$this->dbforge->create_table($table, FALSE, array('ENGINE' => 'InnoDB'));
		$table = $this->db->dbprefix($table);
		$this->db->query(add_foreign_key($table, 'key',
			$this->db->dbprefix(config_item('rest_keys_table')) . '(' . config_item('rest_key_column') . ')', 'CASCADE', 'CASCADE'));
	}


	public function down()
	{
	}

}