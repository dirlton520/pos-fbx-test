<?php
/**
 * @author   Natan Felles <natanfelles@gmail.com>
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Migration_create_table_api_logs
 *
 * @property CI_DB_forge         $dbforge
 * @property CI_DB_query_builder $db
 */
class Migration_create_table_api_logs extends MY_Migration {


	public function up()
	{
		$table = config_item('rest_logs_table');
		$fields = array(
			'id'            => array(
				'type'           => 'INT(11)',
				'auto_increment' => TRUE,
				'unsigned'       => TRUE,
			),
			'api_key'       => array(
				'type' => 'VARCHAR(' . config_item('rest_key_length') . ')',
			),
			'uri'           => array(
				'type' => 'VARCHAR(255)',
			),
			'method'        => array(
				'type' => 'ENUM("get","post","options","put","patch","delete")',
			),
			'params'        => array(
				'type' => 'TEXT',
				'null' => TRUE,
			),
			'ip_address'    => array(
				'type' => 'VARCHAR(45)',
			),
			'time'          => array(
				'type' => 'INT(11)',
			),
			'rtime'         => array(
				'type' => 'FLOAT',
				'null' => TRUE,
			),
			'authorized'    => array(
				'type' => 'VARCHAR(1)',
			),
			'response_code' => array(
				'type'    => 'SMALLINT(3)',
				'null'    => TRUE,
				'default' => 0,
			),
		);
		$this->dbforge->add_field($fields);
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table($table,FALSE,array('ENGINE' => 'InnoDB'));
	}


	public function down()
	{
	}

}
