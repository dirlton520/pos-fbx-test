<?php
/**
 * @author   Natan Felles <natanfelles@gmail.com>
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Migration_create_table_api_limits
 *
 * @property CI_DB_forge         $dbforge
 * @property CI_DB_query_builder $db
 */
class Migration_create_table_api_limits extends MY_Migration {


	public function up()
	{
		$table = config_item('rest_limits_table');
		$fields = array(
			'id'           => array(
				'type'           => 'INT(11)',
				'auto_increment' => TRUE,
				'unsigned'       => TRUE,
			),
			'api_key'      => array(
				'type' => 'VARCHAR(' . config_item('rest_key_length') . ')',
			),
			'uri'          => array(
				'type' => 'VARCHAR(255)',
			),
			'count'        => array(
				'type' => 'INT(10)',
			),
			'hour_started' => array(
				'type' => 'INT(11)',
			),
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->add_key('uri');
		$this->dbforge->create_table($table,FALSE,array('ENGINE' => 'InnoDB'));
		$table = $this->db->dbprefix($table);
		$this->db->query(add_foreign_key($table, 'api_key',
			$this->db->dbprefix(config_item('rest_keys_table')) . '(' . config_item('rest_key_column') . ')', 'CASCADE', 'CASCADE'));
	}


	public function down()
	{
	}

}
