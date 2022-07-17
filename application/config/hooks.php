<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/

		
$hook['pre_system'][] = array(
                                'class'    => '',
                                'function' => 'remote_addr_set',
                                'filename' => 'setup_phppos.php',
                                'filepath' => 'hooks'
                                );

		
$hook['post_controller_constructor'][] = array(
                                'class'    => '',
                                'function' => 'setup_shopify',
                                'filename' => 'setup_phppos.php',
                                'filepath' => 'hooks'
                                );

		
$hook['post_controller_constructor'][] = array(
                                'class'    => '',
                                'function' => 'load_config',
                                'filename' => 'setup_phppos.php',
                                'filepath' => 'hooks'
                                );

$hook['post_controller_constructor'][] = array(
                               'class'    => '',
                               'function' => 'setup_mysql',
                               'filename' => 'setup_phppos.php',
                               'filepath' => 'hooks'
                               );
																					 

$hook['post_controller_constructor'][] = array(
                                'class'    => 'ProfilerEnabler',
                                'function' => 'EnableProfiler',
                                'filename' => 'profiler_enabler.php',
                                'filepath' => 'hooks',
                                'params'   => array()
                                );
$hook['post_controller_constructor'][] = array(
                                'class'    => '',
                                'function' => 'clear_expired_session_data',
                                'filename' => 'setup_phppos.php',
                                'filepath' => 'hooks'
                                );
$hook['post_controller_constructor'][] = array(
    'class'    => '',
    'function' => 'loadLang',
    'filename' => 'bitrix_loadlang.php',
    'filepath' => 'hooks'
);
$hook['post_controller_constructor'][] = array(
    'class'    => '',
    'function' => 'category',
    'filename' => 'bitrix_category.php',
    'filepath' => 'hooks'
);                         
/* End of file hooks.php */
/* Location: ./application/config/hooks.php */