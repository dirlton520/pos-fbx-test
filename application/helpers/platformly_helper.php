<?php

function get_all_platformly_segments()
{
	$CI =& get_instance();
	$cache_key = 'platformly_segments__'.$CI->Location->get_info_for_key('platformly_api_key').'__'.$CI->Employee->get_logged_in_employee_current_location_id();
	if (!$CI->session->userdata($cache_key))
	{
		try
		{
			$post_data = "api_key=".$CI->Location->get_info_for_key('platformly_api_key')."&action=list_segments&value=".json_encode(array('project_id' => $CI->Location->get_info_for_key('platformly_project_id')));
			
			$curl = curl_init();

			//set the url, number of POST vars, POST data
			curl_setopt($curl, CURLOPT_URL, 'https://api.platform.ly');
			curl_setopt($curl,CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl,CURLOPT_POSTFIELDS, $post_data);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
			curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); 
			//Don't verify ssl...just in case a server doesn't have the ability to verify
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			
			curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    		"content-type: application/x-www-form-urlencoded"
  		)); 
			
			$segments = json_decode(curl_exec($curl), TRUE);
			
			
			$CI->session->set_userdata($cache_key,$segments);
		}
		catch(Exception $e)
		{
			return array();
		}
	}
	
	return $CI->session->userdata($cache_key);
}

function email_subscribed_to_segment($email, $segment_id)
{
	$CI =& get_instance();
	
	foreach(get_platformly_segments($email) as $segment)
	{
		if ($segment['id'] == $segment_id)
		{
			return true;
		}
	}
	return false;
}

function get_platformly_segments($email)
{
	static $segments = array();
	$CI =& get_instance();
	try
	{	
		$cache_key_segments = 'platformly_segments__'.$CI->Location->get_info_for_key('platformly_api_key').'__'.$CI->Employee->get_logged_in_employee_current_location_id();
		
		if (!$segments)
		{
			if (!$CI->session->userdata($cache_key_segments))
			{
				$segments = $this->get_all_platformly_segments();
				$CI->session->set_userdata($cache_key_segments, $segments);
			}
			else
			{
				$segments = $CI->session->userdata($cache_key_segments);
			}
		}

		$segments_subscribed = array();
		
		
		$post_data = "api_key=".$CI->Location->get_info_for_key('platformly_api_key')."&action=fetch_contact&value=".json_encode(array('email' => $email));
		
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
		
		//Can process
		if (!(isset($user_profile['status']) && $user_profile['status'] == 'error'))
		{
			foreach($user_profile['project'] as $project)
			{
				if ($project['id'] == $CI->Location->get_info_for_key('platformly_project_id'))
				{
					foreach($project['data']['segments'] as $seg)
					{
						$segments_subscribed[] = $seg;
					}
					
					break;
				}
			}
		}
	}
	catch(Exception $e)
	{
		return array();
	}
	
	
	return $segments_subscribed;
}
?>
