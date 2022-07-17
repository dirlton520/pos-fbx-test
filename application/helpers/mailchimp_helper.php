<?php

use DrewM\MailChimp\MailChimp;

require_once (APPPATH."libraries/MailChimp.php");

function get_all_mailchimps_lists()
{
	$CI =& get_instance();
	$cache_key = 'mailchimp_lists__'.$CI->Location->get_info_for_key('mailchimp_api_key').'__'.$CI->Employee->get_logged_in_employee_current_location_id();
	if (!$CI->session->userdata($cache_key))
	{
		try
		{
			$MailChimp = new MailChimp($CI->Location->get_info_for_key('mailchimp_api_key'));
			$result = $MailChimp->get('lists');
			$lists = $result['lists'];
			$CI->session->set_userdata($cache_key,$lists);
		}
		catch(Exception $e)
		{
			return array();
		}
	}
	
	return $CI->session->userdata($cache_key);
}

function email_subscribed_to_list($email, $list_id)
{
	$CI =& get_instance();
	
	foreach(get_mailchimp_lists($email) as $list)
	{
		if ($list['id'] == $list_id)
		{
			return true;
		}
	}
	return false;
}

function get_mailchimp_lists($email)
{
	static $lists = array();
	$CI =& get_instance();
	try
	{
		$MailChimp = new MailChimp($CI->Location->get_info_for_key('mailchimp_api_key'));
	
		$cache_key_lists = 'mailchimp_lists__'.$CI->Location->get_info_for_key('mailchimp_api_key').'__'.$CI->Employee->get_logged_in_employee_current_location_id();
		
		if (!$lists)
		{
			if (!$CI->session->userdata($cache_key_lists))
			{
				$result = $MailChimp->get('lists');
				$lists = $result['lists'];
				$CI->session->set_userdata($cache_key_lists, $lists);
			}
			else
			{
				$lists = $CI->session->userdata($cache_key_lists);
			}
		}
	    $data = $MailChimp->get('lists', array('email' => $email, 'field' => array('id')));

	    $list_ids_subscribed = array();
		foreach ($data['lists'] as $list) {
		    $list_ids_subscribed[] = $list['id'];
	    }

		$lists_subscribed = array();
		foreach($lists as $list)
		{
			if (in_array($list['id'], $list_ids_subscribed))
			{
				$lists_subscribed[] = $list;
			}
		}
	}
	catch(Exception $e)
	{
		return array();
	}
	
	
	return $lists_subscribed;
}

function get_mailchimp_lists_string($email)
{
	$lists = array();
	foreach(get_mailchimp_lists($email) as $list)
	{
		$lists[] = $list['name'];
	}
	
	return implode(', ', $lists);
}
?>
