<?php
/**
 * Is HTTPS?
 *
 * Determines if the application is accessed via an encrypted
 * (HTTPS) connection.
 *
 * @return	bool
 */
function is_https()
{
	//Cloudflare
	if ( ! empty($_SERVER['HTTP_CF_VISITOR']))
	{	
			$visitor = json_decode($_SERVER['HTTP_CF_VISITOR']);
			if ($visitor !== NULL)
			{
				return $visitor->scheme == 'https';
			}
	}
	
	if ( ! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
	{
		return TRUE;
	}
	elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
	{
		return TRUE;
	}
	elseif ( ! empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off')
	{
		return TRUE;
	}

	return FALSE;
}

function is_on_phppos_host()
{
	if (empty($_SERVER))
	{
		return false;
	}
	if ( isset($_SERVER['CI_PHPPOS_HOST']))
	{
		return $_SERVER['CI_PHPPOS_HOST'];
	}
	
	return isset($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'],'phppointofsale.com') !== FALSE || strpos($_SERVER['HTTP_HOST'],'phppos.com') !== FALSE || strpos($_SERVER['HTTP_HOST'],'phppointofsalestaging.com') !== FALSE);
}

function is_on_api_url()
{
	return strpos($_SERVER['REQUEST_URI'], 'api/v') !== false;
}

function get_real_ip_address()
{
  if (getenv('HTTP_CF_CONNECTING_IP'))
      $ipaddress = getenv('HTTP_CF_CONNECTING_IP');
  else if (getenv('HTTP_CLIENT_IP'))
      $ipaddress = getenv('HTTP_CLIENT_IP');
  else if(getenv('HTTP_X_FORWARDED_FOR'))
      $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
  else if(getenv('HTTP_X_FORWARDED'))
      $ipaddress = getenv('HTTP_X_FORWARDED');
  else if(getenv('HTTP_FORWARDED_FOR'))
      $ipaddress = getenv('HTTP_FORWARDED_FOR');
  else if(getenv('HTTP_FORWARDED'))
     $ipaddress = getenv('HTTP_FORWARDED');
  else if(getenv('REMOTE_ADDR'))
      $ipaddress = getenv('REMOTE_ADDR');
  else
      $ipaddress = FALSE;
  
  return strtok($ipaddress, ',');
}

function hex_encode($input) 
{
  return bin2hex($input);
}

function hex_decode($input) {
  return pack("H*", $input);
}
