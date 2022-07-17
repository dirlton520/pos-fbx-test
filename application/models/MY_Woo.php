<?php
require_once APPPATH.'libraries/wooapi/vendor/autoload.php';

class MY_Woo extends Automattic\WooCommerce\Client
{
	public $CI;
	public $woo_store_url;
	public $woo_api_key;
	public $woo_api_secret;
	public $woo_read_chunk_size;
	public $woo_read_sleep;
	public $woo_write_chunk_size;
	public $woo_curl_timeout;
	public $start_time;
	public $woo_api_version;
	
	public $data;
	public $parameters;
	public $response;
	
	public $batch_create_ids;
	public $batch_update_ids;
	public $batch_delete_ids;
	
	public $woo;
		
	function __construct($woo)
	{
		ini_set('memory_limit','1024M');
		$this->CI =& get_instance();
		$this->CI->load->helper('date');
		
		$this->woo_store_url = $this->CI->config->item('woo_api_url');
		$this->woo_api_key = $this->CI->config->item('woo_api_key');
		$this->woo_api_secret = $this->CI->config->item('woo_api_secret');	
		$this->woo_read_chunk_size = 25;
		$this->woo_read_sleep = 0;
		$this->woo_write_chunk_size = 5;
		$this->woo_write_sleep = 0;
		$this->woo_curl_timeout = 3600;
		$this->start_time = time();
		$this->woo_api_version = $this->CI->config->item('woo_version') == '3.0.0' ? 2 : 1;
				
		$url_parts = parse_url($this->woo_store_url);
		$options  = array('wp_api' => true,'version' => 'wc/v'.$this->woo_api_version, 'timeout' => $this->woo_curl_timeout,'verify_ssl' => FALSE);
		
		if(isset($url_parts['scheme']) && $url_parts['scheme'] == 'https')
		{
			$options['query_string_auth'] = TRUE;
		}
		
		$this->woo = $woo;
		
		parent::__construct($this->woo_store_url, $this->woo_api_key, $this->woo_api_secret, $options);
	}
	
	
	protected function reset()
	{
		$this->data = array();
		$this->parameters = array();
		$this->response = array();
		$this->batch_create_ids = array();
		$this->batch_update_ids = array();
		$this->batch_delete_ids = array();
	}
	
	private function kill_if_needed()
	{
		if ($this->CI->Appconfig->get_raw_kill_ecommerce_cron())
		{
			if (is_cli())
			{
				echo date(get_date_format().' h:i:s ').': KILLING CRON'."\n";
			}
			
			$this->CI->Appconfig->save('kill_ecommerce_cron',0);
			echo json_encode(array('success' => TRUE, 'cancelled' => TRUE, 'sync_date' => date('Y-m-d H:i:s')));
			$this->woo->save_log();
			die();
		}
	}
	
	public function do_batch($endpoint, callable $callback_function = null)
	{		
		if(empty($this->data))
		{
			return $this->response;
		}
				
		$this->woo->log("batch : ". $endpoint);
		
	  $index = 0;
	  $x = 0;
		
		$data_chunked = array();
		
		foreach($this->data as $key => $values)
		{
			foreach($values as $value)
			{
				$data_chunked[$index][$key][] = $value;
				$x++;
				if($x == $this->woo_write_chunk_size)
				{
					$index ++;
					$x=0;
				}
			}
		}
		
		foreach($data_chunked as $data_chunk)
		{
			$this->kill_if_needed();
			sleep($this->woo_write_sleep);
			
			$result = self::post($endpoint, $data_chunk);
			
			if($callback_function)
			{
				$callback_function($result);
			} else {
				$this->response = array_merge_recursive($this->response, $result);
			}
		}
		
		return $this->response;
	}
	
  /**
   * POST method.
   *
   * @param string $endpoint API endpoint.
   * @param array  $data     Request data.
   *
   * @return array
   */
  public function do_post($endpoint)
  {
		if(empty($this->data))
		{
			return $this->response;
		}
		
		$this->woo->log("post : ". $endpoint);		
				
		try
		{
			if (is_cli())
			{
				echo date(get_date_format().' h:i:s ').': post'."\n";
			}

			$this->kill_if_needed();

			sleep($this->woo_write_sleep);


			$result = parent::post($endpoint, $this->data);

			//$response_headers = $this->http->getResponse()->getHeaders();

			return $result;
		}
		catch(Exception $e)
		{
			$this->woo->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
			return array();
		}
  }

  /**
   * PUT method.
   *
   * @param string $endpoint API endpoint.
   * @param array  $data     Request data.
   *
   * @return array
   */
  public function do_put($endpoint)
  {
		if(empty($this->data))
		{
			return $this->response;
		}
		
		$this->woo->log("put : ". $endpoint);
		
		if (is_cli())
		{
			echo date(get_date_format().' h:i:s ').': put'."\n";
		}
		
		$this->kill_if_needed();
		sleep($this->woo_write_sleep);
    return parent::put($endpoint, $this->data);
  }

  /**
   * GET method.
   *
   * @param string $endpoint   API endpoint.
   * @param array  $parameters Request parameters.
   *
   * @return array
   */
  public function do_get($endpoint, $parameters = [])
  {		
		$this->woo->log("get : ". $endpoint);
		
		$send_call = true;
		$page = 1;
				
		$this->parameters['per_page'] = $this->woo_read_chunk_size;
		$this->parameters['context'] = 'view';
		$this->parameters = array_merge($this->parameters, $parameters);
		
		while($send_call == true)
		{			
			try
			{
				if (is_cli())
				{
					echo date(get_date_format().' h:i:s ').': get'."\n";
				}
				
				$this->kill_if_needed();
				
				$this->parameters['page'] = $page;
				$result = parent::get($endpoint, $this->parameters);
				
				$response_headers = $this->http->getResponse()->getHeaders();
			}
			catch(Exception $e)
			{
				$this->woo->log("*******".lang('common_EXCEPTION').": ".var_export($e->getMessage(),TRUE));
				return NULL;
			}
			
			$this->response = array_merge(array_values($this->response), array_values($result));
						
			
			if((isset($response_headers['X-WP-TotalPages']) && $response_headers['X-WP-TotalPages']  > $page) || (isset($response_headers['x-wp-totalpages']) && $response_headers['x-wp-totalpages']  > $page))
			{
				$page++;
				$send_call=true;
				sleep($this->woo_read_sleep);
			} 
			else
			{
				$send_call=false;
			}
		}
		
		
		return $this->response;
  }

  /**
   * DELETE method.
   *
   * @param string $endpoint   API endpoint.
   * @param array  $parameters Request parameters.
   *
   * @return array
   */
  public function do_delete($endpoint, $parameters = [])
  {
		$this->woo->log("delete : ". $endpoint);
		
		if (is_cli())
		{
			echo date(get_date_format().' h:i:s ').': delete'."\n";
		}
		$this->kill_if_needed();
		sleep($this->woo_write_sleep);
    return parent::delete($endpoint, $parameters);
  }

  /**
   * OPTIONS method.
   *
   * @param string $endpoint API endpoint.
   *
   * @return array
   */
  public function options($endpoint)
  {
		if (is_cli())
		{
			echo date(get_date_format().' h:i:s ').': options'."\n";
		}
		$this->kill_if_needed();
    return parent::options($endpoint);
  }
}