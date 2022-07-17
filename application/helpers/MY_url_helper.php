<?php
function current_url()
{
    $CI =& get_instance();

    $url = $CI->config->site_url($CI->uri->uri_string());
    return @$_SERVER['QUERY_STRING'] ? $url.'?'.$_SERVER['QUERY_STRING'] : $url;
}

function secure_app_file_url($file_id,$file_extension=false)
{
    $CI =& get_instance();
  	$CI->load->model('Appfile');
	$signature = $CI->Appfile->get_signature($file_id);
	
	if ($file_extension)
	{
		$app_file_info = $CI->Appfile->get_file_info($file_id);
		return site_url('app_files/view_signed_url/'.$file_id.'/'.rawurlencode($app_file_info->file_name).'?timestamp='.strtotime($app_file_info->timestamp)."&signature=$signature");	
	}
	else
	{
  		return site_url('app_files/view_signed_url/'.$file_id.'?timestamp='.$CI->Appfile->get_file_timestamp($file_id)."&signature=$signature");	

	}
}

function cacheable_app_file_url($file_id)
{
    $CI =& get_instance();
  	$CI->load->model('Appfile');
  	return site_url('app_files/view_cacheable/'.$file_id.'?timestamp='.$CI->Appfile->get_file_timestamp($file_id));	
}

function app_file_url($file_id)
{
  $CI =& get_instance();
	$CI->load->model('Appfile');
	return site_url('app_files/view/'.$file_id.'?timestamp='.$CI->Appfile->get_file_timestamp($file_id));
}
function file_id_to_image_thumb_right($file_id)
{
	return file_id_to_image_thumb($file_id,true);
}
function file_id_to_image_thumb($file_id,$go_right=false)
{
	if ($file_id)
	{
  		$CI =& get_instance();
		$CI->load->model('Appfile');
		$signature = $CI->Appfile->get_signature($file_id);
	
		if ($go_right)
		{
			$go_right = 'go-right';
		}
		else
		{
			$go_right = '';
		}
		$image = site_url('app_files/view_signed_url/'.$file_id.'?timestamp='.$CI->Appfile->get_file_timestamp($file_id)."&signature=$signature");
	
		return "<a href='$image' class='rollover $go_right'><img src='$image' class='img-polaroid' width='120'></a>";
	}
	
	return '';
}

function file_id_to_download_link($file_id)
{
	$CI =& get_instance();
	$CI->load->model('Appfile');
		
	if ($file_id)
	{
		$file = site_url('home/download/'.$file_id.'?timestamp='.$CI->Appfile->get_file_timestamp($file_id));

		return "<a href='$file'>".$CI->Appfile->get_file_info($file_id)->file_name."</a>";
	}
	
	return lang('common_none');
}

function app_file_url_with_extension($file_id)
{
	return secure_app_file_url($file_id,TRUE);
}

function tel($number)
{
	if ($number)
	{
		return '<a href="tel:'.$number.'">'.H($number).'</a>';
	}
	
	return '';
}


function address($address)
{
	if ($address)
	{
		return '<a href="https://www.google.com/maps/place/'.urlencode($address).'" target="_blank">'.H($address).'</a>';
	}
	
	return '';
}

function anchor_or_blank($url)
{
	if ($url)
	{
		$scheme = 'http://';
		$url = parse_url($url, PHP_URL_SCHEME) === null ? $scheme . $url : $url;
		return anchor($url,'',array('target' => '_blank'));
	}
	
	return '';
	
}

function file_get_contents_curl($url)
{
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_URL, $url);
      $data = curl_exec($ch);
      curl_close($ch);
      return $data;
}

?>