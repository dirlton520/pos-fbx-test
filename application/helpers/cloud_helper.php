<?php

function get_cloud_announcement($site_db)
{
	$CI =& get_instance();
	
	$site_db->from('site_config');	
	$site_db->where('key','cloud_announcement_'.$CI->config->item('language'));
	$query = $site_db->get();
	$return = $query->row_array();
	if(isset($return['value']) && $return['value'])
	{
		return $return['value'];
	}
	
	return FALSE;
}
function get_cloud_customer_info($site_db)
{
	$phppos_client_name = substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], '.'));
	$site_db->from('subscriptions');	
	$site_db->where('username',$phppos_client_name);
	$query = $site_db->get();
	return $query->row_array();
}

function is_in_trial($site_db)
{
	$phppos_client_name = substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], '.'));
	$site_db->select('subscr_status');	
	$site_db->from('subscriptions');	
	$site_db->where('username',$phppos_client_name);
	$site_db->where('subscr_status','trial');
	$query = $site_db->get();
	return ($query->num_rows() >= 1);
	
}

function is_trial_over($site_db)
{
	$phppos_client_name = substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], '.'));
	$site_db->select('subscr_status');	
	$site_db->from('subscriptions');	
	$site_db->where('username',$phppos_client_name);
	$site_db->where('subscr_status','trial');
	$site_db->where('trial_end_date < ',date('Y-m-d'));
	$query = $site_db->get();
	return ($query->num_rows() >= 1);
}

function is_subscription_cancelled($site_db)
{
	$phppos_client_name = substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], '.'));
	$site_db->select('subscr_status');	
	$site_db->from('subscriptions');	
	$site_db->where('username',$phppos_client_name);
	$site_db->where('subscr_status','cancelled');
	$query = $site_db->get();
	return ($query->num_rows() >= 1);
}

function is_subscription_cancelled_within_grace_period($site_db)
{
	$phppos_client_name = substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], '.'));
	$grace_period = date('Y-m-d H:i:s', strtotime("now -3 days"));
	$site_db->select('subscr_status');	
	$site_db->from('subscriptions');	
	$site_db->where('username',$phppos_client_name);
	$site_db->where('subscr_status','cancelled');
	$site_db->where('cancel_date >', $grace_period);
	$query = $site_db->get();
	return ($query->num_rows() >= 1);
}

function is_subscription_failed($site_db)
{
	$phppos_client_name = substr($_SERVER['HTTP_HOST'], 0, strpos($_SERVER['HTTP_HOST'], '.'));
	$site_db->select('subscr_status');	
	$site_db->from('subscriptions');	
	$site_db->where('username',$phppos_client_name);
	$site_db->where('subscr_status','failed');
	$query = $site_db->get();
	return ($query->num_rows() >= 1);
}