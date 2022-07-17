<?php

function redisNoticeFunction($errno, $errstr, $errfile, $errline, array $errcontext) 
{
    // is the error E_NOTICE?
    if ($errno === E_NOTICE) 
	{
        // if error was suppressed with the @-operator
        if (0 === error_reporting()) 
		{
            // do nothing, continue execution of script
            return;
        }
    
        // if notice was about about Redis locking
        if ($errstr == 'session_start(): Acquire of session lock was not successful') 
		{
            $error_msg = 'Redis locking problem with notice msg: ' . $errstr;
            $error_msg .= ' at ' . $errfile . ':' . $errline . \PHP_EOL;

            // signal fatal error
            $error_type = E_USER_ERROR;
            trigger_error ($error_msg, $error_type);
        }
        return;
    }
}

class MY_Session extends CI_Session 
{
	public function __construct(array $params = array())
	{
		$CI =& get_instance();
		
		if (!is_on_phppos_host())
		{
			if (!$CI->db->table_exists('sessions') || !$CI->db->field_exists('data','sessions'))
			{
				log_message('debug', 'Session: Initialization aborted; no table.');
				return;
			}
		}
		
		//Use native php sessions
		if (is_on_phppos_host())
		{
			$current_error_reporting = error_reporting();
			error_reporting(E_ALL);
			set_error_handler('redisNoticeFunction');
			
			//Settings for redis sessions
			ini_set('session.save_handler','redis');
			$redis_host = isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == 'localhost' ? '127.0.0.1:6379' : 'php-pos-redis-cache.i1at3u.ng.0001.use1.cache.amazonaws.com:6379';
			ini_set('session.save_path','tcp://'.$redis_host.'?prefix=PHPPOSSESSION:');
			ini_set('redis.session.locking_enabled',1);
			
			//How long should the lock live (in seconds)? Defaults to: value of max_execution_time.
			ini_set('redis.session.lock_expire',300);
			
			//How long to wait between attempts to acquire lock, in microseconds (µs)?. Defaults to: 2000
			ini_set('redis.session.lock_wait_time',80000);
			
			//Maximum number of times to retry (-1 means infinite). Defaults to: 10
			ini_set('redis.session.lock_retries',150);
			
			// No sessions under CLI
			if (is_cli())
			{
				log_message('debug', 'Session: Initialization under CLI aborted.');
				return;
			}
			elseif ((bool) ini_get('session.auto_start'))
			{
				log_message('error', 'Session: session.auto_start is enabled in php.ini. Aborting.');
				return;
			}
			
			// Configuration ...
			$this->_configure($params);
			$this->_config['_sid_regexp'] = $this->_sid_regexp;

			// Sanitize the cookie, because apparently PHP doesn't do that for userspace handlers
			if (isset($_COOKIE[$this->_config['cookie_name']])
				&& (
					! is_string($_COOKIE[$this->_config['cookie_name']])
					OR ! preg_match('#\A'.$this->_sid_regexp.'\z#', $_COOKIE[$this->_config['cookie_name']])
				)
			)
			{
				unset($_COOKIE[$this->_config['cookie_name']]);
			}
			
			//Disconnect before session start in case we lock we don't keep an open connection
			$CI->db->close();
			session_start();
			
			//Use regular error handling if session_start() does not end in a lock
			restore_error_handler();
			error_reporting($current_error_reporting);
			//connect back up
			$CI->load->database();
			
			$gc_probability = ini_get('session.gc_probability');
			$gc_divisor = ini_get('session.gc_divisor');
			$probability = $gc_probability/$gc_divisor;
			$random_float_between_0_and_1 = mt_rand() / mt_getrandmax();
			
			if ($random_float_between_0_and_1 <= $probability)
			{
				$this->cleanup_expired_files();
			}
			
			
		  if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'])
		  {
			  if (!$this->userdata('domain'))
			  {
				  $this->set_userdata('domain', $_SERVER['HTTP_HOST']);
	      }
		  
			  if ($this->userdata('domain') != $_SERVER['HTTP_HOST'])
			  {
				  die(lang('common_session_hijacking_attempt_no_access_allowed'));
			  }
		  }

			// Is session ID auto-regeneration configured? (ignoring ajax requests)
			if ((empty($_SERVER['HTTP_X_REQUESTED_WITH']) OR strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest')
				&& ($regenerate_time = config_item('sess_time_to_update')) > 0
			)
			{
				if ( ! isset($_SESSION['__ci_last_regenerate']))
				{
					$_SESSION['__ci_last_regenerate'] = time();
				}
				elseif ($_SESSION['__ci_last_regenerate'] < (time() - $regenerate_time))
				{
					$this->sess_regenerate((bool) config_item('sess_regenerate_destroy'));
				}
			}
			// Another work-around ... PHP doesn't seem to send the session cookie
			// unless it is being currently created or regenerated
			elseif (isset($_COOKIE[$this->_config['cookie_name']]) && $_COOKIE[$this->_config['cookie_name']] === session_id())
			{
				setcookie(
					$this->_config['cookie_name'],
					session_id(),
					(empty($this->_config['cookie_lifetime']) ? 0 : time() + $this->_config['cookie_lifetime']),
					$this->_config['cookie_path'],
					$this->_config['cookie_domain'],
					$this->_config['cookie_secure'],
					TRUE
				);
			}

			$this->_ci_init_vars();
			log_message('info', "Session: Class initialized using native php.");
		}
		else
		{
			parent::__construct($params);
		}
	}
	
	/**
	 * Configuration
	 *
	 * Handle input parameters and configuration defaults
	 *
	 * @param	array	&$params	Input parameters
	 * @return	void
	 */
	protected function _configure(&$params)
	{
		$CI =& get_instance();
		$CI->load->model('Appconfig');
		$phppos_session_expiration = $CI->db->table_exists('app_config') ? $CI->Appconfig->get_raw_phppos_session_expiration() : 0;		
		$expiration = $phppos_session_expiration !== NULL ? $phppos_session_expiration : config_item('sess_expiration');
		$CI->config->set_item('sess_expiration',$expiration);
		
		parent::_configure($params);
	}
	
	function cleanup_expired_files()
	{
		$return = TRUE;
		$cur_timezone = date_default_timezone_get();
		
		$CI =& get_instance();
		date_default_timezone_set('America/New_York');
		if ($CI->db->table_exists('app_files') && $CI->db->field_exists('expires','app_files'))
		{		
			$return = $CI->db->delete('app_files', 'expires < '.$CI->db->escape(date('Y-m-d H:i:s')).' and expires IS NOT NULL');
		}
		
		date_default_timezone_set($cur_timezone);
		
		return $return;
	}
	
	
	/**
	 * Handle temporary variables
	 *
	 * Clears old "flash" data, marks the new one for deletion and handles
	 * "temp" data deletion.
	 *
	 * @return	void
	 */
	protected function _ci_init_vars()
	{
		if ( ! empty($_SESSION['__ci_vars']))
		{
			$current_time = time();

			foreach ($_SESSION['__ci_vars'] as $key => &$value)
			{
				if ($value === 'new')
				{
					$_SESSION['__ci_vars'][$key] = 'old';
				}
				//This is what we had to change for php 8. The hacky method in parent class didn't work
				elseif ($value === 'old')
				{
					unset($_SESSION[$key], $_SESSION['__ci_vars'][$key]);
				}
			}

			if (empty($_SESSION['__ci_vars']))
			{
				unset($_SESSION['__ci_vars']);
			}
		}

		$this->userdata =& $_SESSION;
	}
}
