<?php
require_once (APPPATH."libraries/MailChimp.php");
use DrewM\MailChimp\MailChimp;

class Person extends MY_Model
{
	/*Determines whether the given person exists*/
	function exists($person_id)
	{
		$this->db->from('people');	
		$this->db->where('people.person_id',$person_id);
		$query = $this->db->get();
		
		return ($query->num_rows()==1);
	}
	
	/*Gets all people*/
	function get_all($limit=10000, $offset=0)
	{
		$this->db->from('people');
		$this->db->order_by("last_name", "asc");
		$this->db->limit($limit);
		$this->db->offset($offset);
		return $this->db->get();		
	}
	
	function count_all()
	{
		$this->db->from('people');
		$this->db->where('deleted',0);
		return $this->db->count_all_results();
	}
	
	/*
	Gets information about a person as an array.
	*/
	function get_info($person_id)
	{
		$query = $this->db->get_where('people', array('person_id' => $person_id), 1);
		
		if($query->num_rows()==1)
		{
			return $query->row();
		}
		else
		{
			//create object with empty properties.
			$fields = array('first_name','last_name','full_name','phone_number','email','address_1','address_2','city','state','zip','country','comments','image_id','person_id','create_date','last_modified');
			
			$person_obj = new stdClass;
			
			foreach ($fields as $field)
			{
				$person_obj->$field='';
			}
			
			return $person_obj;
		}
	}
	
	/*
	Get people with specific ids
	*/
	function get_multiple_info($person_ids)
	{
		$this->db->from('people');
		$this->db->where_in('person_id',$person_ids);
		$this->db->order_by("last_name", "asc");
		return $this->db->get();		
	}
	
	/*
	Inserts or updates a person
	*/
	function save(&$person_data,$person_id=false,$return_data=false)
	{		
		if(!empty($person_data))
		{
			if (isset($person_data['first_name']) && isset($person_data['last_name']))
			{
				$person_data['full_name'] = $person_data['first_name'].' '.$person_data['last_name'];
			}
			elseif(isset($person_data['first_name']))
			{
				$cur_person_info = $this->get_info($person_id);
				$last_name = $cur_person_info->last_name;
				$person_data['full_name'] = $person_data['first_name'].' '.$last_name;
			}
			elseif(isset($person_data['last_name']))
			{
				$cur_person_info = $this->get_info($person_id);
				$first_name = $cur_person_info->first_name;
				$person_data['full_name'] = $first_name.' '.$person_data['last_name'];
			}
		
			if (!$person_id or !$this->exists($person_id))
			{
				$person_data['create_date'] = date('Y-m-d H:i:s');
				if ($this->db->insert('people',$person_data))
				{
					$person_data['person_id']=$this->db->insert_id();
					if($return_data==true){
						return $person_data;
					}
					return true;
				}
			
				return false;
			}
			$person_data['last_modified'] = date('Y-m-d H:i:s');
			$this->db->where('person_id', $person_id);
			return $this->db->update('people',$person_data);
		}
		
		return true;
	}
	
	/*
	Deletes one Person (doesn't actually do anything)
	*/
	function delete($person_id)
	{
		return true;; 
	}
	
	/*
	Deletes a list of people (doesn't actually do anything)
	*/
	function delete_list($person_ids)
	{	
		return true;	
 	}

	function update_platformly_subscriptions($email, $first_name, $last_name, $segment_ids)
	{
		$this->load->helper('platformly');
		$segment_ids = $segment_ids == FALSE ? array() : $segment_ids;
		$current_segments = get_platformly_segments($email);
		
		$post_data = "api_key=".$this->Location->get_info_for_key('platformly_api_key')."&action=add_contact&value=".json_encode(array('email'=>$email,'first_name' => $first_name,'last_name' => $last_name,'project_id' => $this->Location->get_info_for_key('platformly_project_id')));
	
		$curl = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($curl, CURLOPT_URL, 'https://api.platform.ly');
		curl_setopt($curl,CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl,CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); 
	
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
  		"content-type: application/x-www-form-urlencoded"
		)); 
	
		$user_profile = json_decode(curl_exec($curl), TRUE);
		$contact_id = $user_profile['data']['cc_id'];
		
		$post_data = "api_key=".$this->Location->get_info_for_key('platformly_api_key')."&action=contact_segment_removeall&value=".json_encode(array('email'=>$email));
	
		$curl = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($curl, CURLOPT_URL, 'https://api.platform.ly');
		curl_setopt($curl,CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl,CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); 
	
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
  		"content-type: application/x-www-form-urlencoded"
		)); 
	
		curl_exec($curl);		
		
		if (count($segment_ids))
		{
			$post_data = "api_key=".$this->Location->get_info_for_key('platformly_api_key')."&action=contact_segment_add&value=".json_encode(array('email'=>$email,'segment' => implode(',',$segment_ids)));
			$curl = curl_init();

			//set the url, number of POST vars, POST data
			curl_setopt($curl, CURLOPT_URL, 'https://api.platform.ly');
			curl_setopt($curl,CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl,CURLOPT_POSTFIELDS, $post_data);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
			curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); 
	
			curl_setopt($curl, CURLOPT_HTTPHEADER, array(
	  		"content-type: application/x-www-form-urlencoded"
			)); 
	
			curl_exec($curl);
		}		
		
	}
	
	function update_mailchimp_subscriptions($email, $first_name, $last_name, $mailing_list_ids)
	{
		$this->load->helper('mailchimp');
		$mailing_list_ids = $mailing_list_ids == FALSE ? array() : $mailing_list_ids;
		$current_lists = get_mailchimp_lists($email);
		$mailchimp = new MailChimp($this->Location->get_info_for_key('mailchimp_api_key'));
		foreach($current_lists as $list)
		{
			//If a list we are currently subscribed to is not in the updated list, unsubscribe
			if (!in_array($list['id'], $mailing_list_ids))
			{
				$mailchimp->delete('/lists/'.$list['id'].'/members/'.md5(strtolower($email)));
			}
		}
		
		foreach($mailing_list_ids as $list)
		{
			$data = $mailchimp->put(
				'/lists/'.$list.'/members/'.md5(strtolower($email)),
				array(
					'email_address' => $email,
					'status' => 'subscribed',
					'merge_fields' => array(
						'FNAME' => $first_name,
						'LNAME' => $last_name
					),
					'email_type' => 'html',
				)
			);
		}
	}
	
	function update_image($file_id,$person_id)
	{
		$this->db->set('image_id',$file_id);
		$this->db->where('person_id',$person_id);
		return $this->db->update('people');
	}
	
	function add_file($person_id,$file_id)
	{
		$this->db->insert('people_files', array('file_id' => $file_id, 'person_id' => $person_id));
	}
	
	function delete_file($file_id)
	{
	  $this->db->where('file_id',$file_id);
		$this->db->delete('people_files');
		$this->load->model('Appfile');
		return $this->Appfile->delete($file_id);
	}
	
	function get_files($person_id)
	{
		$this->db->select('people_files.*,app_files.file_name');
		$this->db->from('people_files');
		$this->db->join('app_files','app_files.file_id = people_files.file_id');
		$this->db->where('person_id',$person_id);
		$this->db->order_by('people_files.id');
		return $this->db->get();
	}	
	
	function get_person_search_suggestions($search,$limit=25)
	{
		
		if (!trim($search))
		{
			return array();
		}
			
		
		$suggestions = array();
		$customers = $this->Customer->get_customer_search_suggestions($search,0,$limit);
		$suppliers = $this->Supplier->get_supplier_search_suggestions($search,0,$limit);
		$employees = $this->Employee->get_search_suggestions($search,0,$limit);
		
		$suggestions = array_merge($customers,$suppliers,$employees);
		return $suggestions;

	}
	
	
}
?>
