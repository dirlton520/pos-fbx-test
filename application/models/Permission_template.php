<?php
class Permission_template extends MY_Model
{
	/*
	Determines if a given template_id is an template
	*/
	function exists($template_id)
	{
		$this->db->from('permissions_templates');
		$this->db->where('id', $template_id);
		$query = $this->db->get();
		return ($query->num_rows() == 1);
	}

	/*
	Returns all the template
	*/
	function get_all($deleted = 0, $limit = 10000, $offset = 0, $col = 'name', $order = 'asc')
	{
		if (!$deleted) {
			$deleted = 0;
		}

		$order_by = '';
		if (!$this->config->item('speed_up_search_queries')) {
			$order_by = " ORDER BY " . $col . " " . $order;
		}

		$permissions_templates = $this->db->dbprefix('permissions_templates');

		$data = $this->db->query("SELECT * FROM $permissions_templates WHERE deleted = $deleted $order_by  LIMIT  $offset, $limit");

		return $data;
	}

	function count_all($deleted = 0)
	{
		if (!$deleted) {
			$deleted = 0;
		}

		$this->db->from('permissions_templates');
		$this->db->where('deleted', $deleted);
		return $this->db->count_all_results();
	}


	function get_info($permission_template_id)
	{
		$query = $this->db->get_where('permissions_templates', array('id' => $permission_template_id), 1);

		if ($query->num_rows() == 1) {
			return $query->row();
		} else {
			//create object with empty properties.
			$fields = array('id', 'name', 'deleted');

			$person_obj = new stdClass;

			foreach ($fields as $field) {
				$person_obj->$field = '';
			}

			return $person_obj;
		}
	}

	/*
	Inserts or updates an permission template
	*/
	function save($template_data, $permission_data = array(), $permission_action_data = array(), $template_id = false, $action_location = array(), $module_location = array(), $update_employee_permission = false)
	{
		$success = false;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		if ($template_id === -1 or !$this->exists($template_id)) {
			$success = $this->db->insert('permissions_templates', $template_data);
			$template_id = $this->db->insert_id();
		} else {
			if (!empty($template_data) && $this->exists($template_id)) {
				$this->db->where('id', $template_id);
				$success = $this->db->update('permissions_templates', $template_data);
			} else {
				$success = TRUE;
			}
		}


		//We have either inserted or updated a new template, now lets set permissions.
		if ($success) {
			//First lets clear out any permissions the template currently has.
			$success = $this->db->delete('permissions_template', array('template_id' => $template_id));

			//Now insert the new permissions
			if ($success) {
				foreach ($permission_data as $allowed_module) {
					$success = $this->db->insert(
						'permissions_template',
						array(
							'module_id' => $allowed_module,
							'template_id' => $template_id
						)
					);
				}
			}

			//First lets clear out any permissions actions the template currently has.
			$success = $this->db->delete('permissions_template_actions', array('template_id' => $template_id));

			//Now insert the new permissions actions
			if ($success) {
				foreach ($permission_action_data as $permission_action) {
					list($module, $action) = explode('|', $permission_action);
					$success = $this->db->insert(
						'permissions_template_actions',
						array(
							'module_id' => $module,
							'action_id' => $action,
							'template_id' => $template_id
						)
					);
				}
			}


			//module_loation array
			$data_permissions_locations = array();
			foreach ($module_location as $mlk => $mlv) {
				$element = explode("|", $mlv);
				$data_permissions_locations[] = array(
					'module_id' => $element[0],
					'template_id' => $template_id,
					'location_id' => $element[1]
				);
			}

			//action_location array
			$data_permissions_actions_locations = array();
			foreach ($action_location as $alk => $alv) {
				$element = explode("|", $alv);

				$data_permissions_actions_locations[] = array(
					'module_id' => $element[0],
					'action_id' => $element[1],
					'location_id' => $element[2],
					'template_id' => $template_id
				);
			}

			if (!empty($data_permissions_locations)) {
				//permissions_locations module_id, template_id, location_id
				$success = $this->db->delete('permissions_template_locations', array('template_id' => $template_id));
				$this->db->insert_batch('permissions_template_locations', $data_permissions_locations);
			}

			if (!empty($data_permissions_actions_locations)) {
				//permissions_actions_locations module_id, template_id, action_id, location_id
				$success = $this->db->delete('permissions_template_actions_locations', array('template_id' => $template_id));
				$this->db->insert_batch('permissions_template_actions_locations', $data_permissions_actions_locations);
			}












			if ($update_employee_permission && $template_id != -1) {
				//update employee permissions related to this template
				foreach ($this->get_employees_by_template_id($template_id) as $ek => $ev) {
					//First lets clear out any permissions the employee currently has.
					$employee_id = $ev->person_id;
					$success = $this->db->delete('permissions', array('person_id' => $employee_id));

					//Now insert the new permissions
					if ($success) {
						foreach ($permission_data as $allowed_module) {
							$success = $this->db->insert(
								'permissions',
								array(
									'module_id' => $allowed_module,
									'person_id' => $employee_id
								)
							);
						}
					}

					//First lets clear out any permissions actions the employee currently has.
					$success = $this->db->delete('permissions_actions', array('person_id' => $employee_id));

					//Now insert the new permissions actions
					if ($success) {
						foreach ($permission_action_data as $permission_action) {
							list($module, $action) = explode('|', $permission_action);
							$success = $this->db->insert(
								'permissions_actions',
								array(
									'module_id' => $module,
									'action_id' => $action,
									'person_id' => $employee_id
								)
							);
						}
					}


					//module_loation array
					$data_permissions_locations = array();
					foreach ($module_location as $mlk => $mlv) {
						$element = explode("|", $mlv);
						$data_permissions_locations[] = array(
							'module_id' => $element[0],
							'person_id' => $employee_id,
							'location_id' => $element[1]
						);
					}

					//action_location array
					$data_permissions_actions_locations = array();
					foreach ($action_location as $alk => $alv) {
						$element = explode("|", $alv);

						$data_permissions_actions_locations[] = array(
							'module_id' => $element[0],
							'action_id' => $element[1],
							'location_id' => $element[2],
							'person_id' => $employee_id
						);
					}

					if (!empty($data_permissions_locations)) {
						//permissions_locations module_id, person_id, location_id
						$success = $this->db->delete('permissions_locations', array('person_id' => $employee_id));
						$this->db->insert_batch('permissions_locations', $data_permissions_locations);
					}

					if (!empty($data_permissions_actions_locations)) {
						//permissions_actions_locations module_id, person_id, action_id, location_id
						$success = $this->db->delete('permissions_actions_locations', array('person_id' => $employee_id));
						$this->db->insert_batch('permissions_actions_locations', $data_permissions_actions_locations);
					}
				}
			}
		}

		$this->db->trans_complete();
		return $success;
	}

	/*
	Deletes one template
	*/
	function delete($id)
	{
		$this->db->where('id', $id);
		return $this->db->update('permissions_templates', array('deleted' => 1));
	}

	/*
	Deletes a list of templates
	*/
	function delete_list($ids)
	{
		$this->db->where_in('id', $ids);
		return $this->db->update('permissions_templates', array('deleted' => 1));
	}

	/*
	undeletes one template
	*/
	function undelete($id)
	{
		$this->db->where('id', $id);
		return $this->db->update('permissions_templates', array('deleted' => 0));
	}

	/*
	undeletes a list of templates
	*/
	function undelete_list($ids)
	{
		$this->db->where_in('id', $ids);
		return $this->db->update('permissions_templates', array('deleted' => 0));
	}

	function check_duplicate($term)
	{
		$this->db->from('permissions_templates');
		$this->db->where('deleted', 0);
		$query = $this->db->where("name = " . $this->db->escape($term));
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return true;
		}
	}

	/*
	Get search suggestions to find templates
	*/
	function get_search_suggestions($search, $deleted = 0, $limit = 5)
	{
		if (!trim($search)) {
			return array();
		}

		if (!$deleted) {
			$deleted = 0;
		}

		$suggestions = array();

		$this->db->select("id, name", FALSE);
		$this->db->from('permissions_templates');

		$this->db->where("name LIKE '" . $this->db->escape_like_str($search) . "%' and deleted=$deleted");

		$this->db->limit($limit);

		$by_name = $this->db->get();
		$temp_suggestions = array();
		foreach ($by_name->result() as $row) {
			$data = array(
				'name' => $row->name
			);
			$temp_suggestions[$row->id] = $data;
		}


		$this->load->helper('array');
		uasort($temp_suggestions, 'sort_assoc_array_by_name');

		foreach ($temp_suggestions as $key => $value) {
			$suggestions[] = array('value' => $key, 'label' => $value['name'], 'subtitle' => 'Permission Template', 'avatar' => base_url()."assets/img/user.png");
		}

		//only return $limit suggestions
		$suggestions = array_map("unserialize", array_unique(array_map("serialize", $suggestions)));
		if (count($suggestions) > $limit) {
			$suggestions = array_slice($suggestions, 0, $limit);
		}

		$suggestions = array_map("unserialize", array_unique(array_map("serialize", $suggestions)));

		return $suggestions;
	}

	function search($search, $deleted = 0, $limit = 20, $offset = 0, $column = 'name', $orderby = 'asc', $search_field = NULL)
	{
		if (!$deleted) {
			$deleted = 0;
		}

		//The queries are done as 2 unions to speed up searches to use indexes.
		//When doing OR WHERE across 2 tables; performance is not good
		$this->db->select('id,name,deleted');
		$this->db->from('permissions_templates');

		if ($search) {
			if ($search_field) {
				$this->db->where("$search_field LIKE '" . $this->db->escape_like_str($search) . "%' and deleted=$deleted");
			} else {
				$this->db->where(" name LIKE '" . $this->db->escape_like_str($search) . "%' and deleted=$deleted");
			}
		} else {
			$this->db->where('deleted', $deleted);
		}

		$permission_template_search = $this->db->get_compiled_select();

		$order_by = '';
		if (!$this->config->item('speed_up_search_queries')) {
			$order_by = " ORDER BY $column $orderby ";
		}

		return $this->db->query($permission_template_search . $order_by . " LIMIT $limit OFFSET $offset");
	}

	function search_count_all($search, $deleted = 0)
	{
		if (!$deleted) {
			$deleted = 0;
		}

		//The queries are done as 2 unions to speed up searches to use indexes.
		//When doing OR WHERE across 2 tables; performance is not good
		$this->db->from('permissions_templates');

		if ($search) {
			$this->db->where("name LIKE '" . $this->db->escape_like_str($search) . "%' and deleted = $deleted");
		} else {
			$this->db->where('deleted', $deleted);
		}

		$permissions_template_search = $this->db->get_compiled_select();

		$result = $this->db->query($permissions_template_search);
		return $result->num_rows();
	}

	/*
	Determins whether the template specified template has access the specific module.
	*/
	function has_module_permission($module_id, $permission_template_id, $location_id = FALSE, $global_only = FALSE)
	{
		//if no module_id is null, allow access
		if ($module_id == null) {
			return true;
		}

		if ($location_id === FALSE) {
			$location_id = 1;
		}

		static $cache;

		if (isset($cache[$module_id . '|' . $permission_template_id . '|' . $location_id . '|' . ($global_only ? '1' : '0')])) {
			return $cache[$module_id . '|' . $permission_template_id . '|' . $location_id . '|' . ($global_only ? '1' : '0')];
		}


		if ($global_only) {
			$query = $this->db->get_where('permissions_template', array('template_id' => $permission_template_id, 'module_id' => $module_id), 1);
			$cache[$module_id . '|' . $permission_template_id . '|' . $location_id . '|' . ($global_only ? '1' : '0')] =  $query->num_rows() == 1;
		} else {
			//Don't include location id to see if anywhere overrides it
			$this->db->from('permissions_template_locations');
			$this->db->where("permissions_template_locations.template_id", $permission_template_id);
			$this->db->where('permissions_template_locations.module_id', $module_id);

			$query = $this->db->get();

			//Can be overwritten at many locations
			$is_overridden = $query->num_rows() >= 1;

			if ($is_overridden) {
				$this->db->from('permissions_template_locations');
				$this->db->where("permissions_template_locations.template_id", $permission_template_id);
				$this->db->where('permissions_template_locations.module_id', $module_id);
				$this->db->where('permissions_template_locations.location_id', $location_id);

				$query = $this->db->get();
				$cache[$module_id . '|' . $permission_template_id . '|' . $location_id . '|' . ($global_only ? '1' : '0')] =   $query->num_rows() == 1;
			} else {
				$query = $this->db->get_where('permissions_template', array('template_id' => $permission_template_id, 'module_id' => $module_id), 1);
				$cache[$module_id . '|' . $permission_template_id . '|' . $location_id . '|' . ($global_only ? '1' : '0')] =  $query->num_rows() == 1;
			}
		}

		return $cache[$module_id . '|' . $permission_template_id . '|' . $location_id . '|' . ($global_only ? '1' : '0')];
	}

	function has_module_action_permission($module_id, $action_id, $permission_template_id, $location_id = FALSE, $global_only = FALSE)
	{
		//if no module_id is null, allow access
		if ($module_id == null) {
			return true;
		}

		if ($location_id === FALSE) {
			$location_id = 1;
		}

		static $cache;

		if (isset($cache[$module_id . '|' . $action_id . '|' . $permission_template_id . '|' . $location_id . '|' . ($global_only ? '1' : '0')])) {
			return $cache[$module_id . '|' . $action_id . '|' . $permission_template_id . '|' . $location_id . '|' . ($global_only ? '1' : '0')];
		}

		if ($global_only) {
			$this->db->select('permissions_template_actions.*');
			$this->db->from('permissions_template_actions');
			$this->db->where("permissions_template_actions.template_id", $permission_template_id);
			$this->db->where('permissions_template_actions.module_id', $module_id);
			$this->db->where('permissions_template_actions.action_id', $action_id);
			$query = $this->db->get();
			$cache[$module_id . '|' . $action_id . '|' . $permission_template_id . '|' . $location_id . '|' . ($global_only ? '1' : '0')] =  $query->num_rows() == 1;
		} else {
			//Don't include location id to see if anywhere overrides it
			$this->db->from('permissions_template_actions_locations');
			$this->db->where("permissions_template_actions_locations.template_id", $permission_template_id);
			$this->db->where('permissions_template_actions_locations.module_id', $module_id);
			$this->db->where('permissions_template_actions_locations.action_id', $action_id);
			$query = $this->db->get();

			//Can be overwritten at many locations
			$is_overridden = $query->num_rows() >= 1;

			if ($is_overridden) {
				$this->db->from('permissions_template_actions_locations');
				$this->db->where('permissions_template_actions_locations.location_id', $location_id);
				$this->db->where("permissions_template_actions_locations.template_id", $permission_template_id);
				$this->db->where('permissions_template_actions_locations.module_id', $module_id);
				$this->db->where('permissions_template_actions_locations.action_id', $action_id);
				$query = $this->db->get();
				$cache[$module_id . '|' . $action_id . '|' . $permission_template_id . '|' . $location_id . '|' . ($global_only ? '1' : '0')] =   $query->num_rows() == 1;
			} else {
				$this->db->select('permissions_template_actions.*');
				$this->db->from('permissions_template_actions');
				$this->db->where("permissions_template_actions.template_id", $permission_template_id);
				$this->db->where('permissions_template_actions.module_id', $module_id);
				$this->db->where('permissions_template_actions.action_id', $action_id);
				$query = $this->db->get();
				$cache[$module_id . '|' . $action_id . '|' . $permission_template_id . '|' . $location_id . '|' . ($global_only ? '1' : '0')] =  $query->num_rows() == 1;
			}
		}

		return $cache[$module_id . '|' . $action_id . '|' . $permission_template_id . '|' . $location_id . '|' . ($global_only ? '1' : '0')];
	}

	function get_action_wise_template_location($template_id, $module_id = null)
	{
		$this->db->from('permissions_template_locations');
		$this->db->where('template_id', $template_id);
		$data['permissions_locations'] = $this->db->get()->result();


		if ($module_id != null) {
			$this->db->from('permissions_template_actions_locations');
			$this->db->where(array('template_id' => $template_id, 'module_id' => $module_id));
			$data['permissions_actions_locations'] = $this->db->get()->result();
		} else {
			$this->db->from('permissions_template_actions_locations');
			$this->db->where('template_id', $template_id);
			$data['permissions_actions_locations'] = $this->db->get()->result();
		}

		return $data;
	}

	function check_action_has_template_location($result_set, $module_id, $action_id, $location_id)
	{
		$result_set = $result_set['permissions_actions_locations'];
		foreach ($result_set as $rk => $rv) {
			if ($module_id == $rv->module_id && $action_id == $rv->action_id && $location_id == $rv->location_id) {
				return true;
			}
		}
		return false;
	}

	function check_module_has_location($result_set, $module_id, $location_id)
	{
		$result_set = $result_set['permissions_locations'];
		foreach ($result_set as $rk => $rv) {
			if ($module_id == $rv->module_id && $location_id == $rv->location_id) {
				return true;
			}
		}
		return false;
	}

	function get_employees_by_template_id($template_id)
	{
		$result = array();
		$this->db->select('person_id');
		$this->db->from('employees');
		$this->db->where(array('template_id' => $template_id));
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			$result = $query->result();
		}
		return $result;
	}
}
