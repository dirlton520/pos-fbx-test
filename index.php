<?php
if (function_exists('newrelic_set_appname'))
{
	if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'])
	{
		newrelic_set_appname($_SERVER['HTTP_HOST'].';'.'PHP Point Of Sale Cloud');
	}
}

// Set the current directory correctly for CLI requests
if (defined('STDIN'))
{
	chdir(dirname(__FILE__));
}

require_once realpath('application').'/core/MY_Common.php';

if (is_on_phppos_host() && is_on_api_url())
{
	include realpath('application').'/config/database.php';
	$phppos_db_link = mysqli_connect($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);
	$migration_result = mysqli_query($phppos_db_link, "SELECT `version` FROM phppos_migrations");
	mysqli_close($phppos_db_link);
	if ($migration_result)
	{
		$db_migration_row = mysqli_fetch_assoc($migration_result);
		$db_migration_version = $db_migration_row['version'];
	}
	else
	{
		$db_migration_version = NULL;
	}
	
	$config = array('migration_version' => '0');
	include realpath('application').'/config/migration.php';
	$app_migration_version = $config['migration_version'];
	if ($db_migration_version !== NULL && $db_migration_version!=$app_migration_version)
	{
		if ($db_migration_version > $app_migration_version)
		{
			chdir('..');
		}
		else
		{
			define('IS_PREVIOUS', TRUE);
			$prev_folder = isset($_SERVER['CI_PREV_FOLDER']) ?  $_SERVER['CI_PREV_FOLDER'] : 'PHP-Point-Of-Sale-Prev';
			chdir($prev_folder);
		}
	}
}
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2017, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */

/*
 *---------------------------------------------------------------
 * APPLICATION ENVIRONMENT
 *---------------------------------------------------------------
 *
 * You can load different configurations depending on your
 * current environment. Setting the environment also influences
 * things like logging and error reporting.
 *
 * This can be set to anything, but default usage is:
 *
 *     development
 *     testing
 *     production
 *
 * NOTE: If you change these, also change the error_reporting() code below
 */
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'production');

/*
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but testing and live will hide them.
 */
switch (ENVIRONMENT)
{
	case 'development':
		error_reporting(-1);
		ini_set('display_errors', 1);
	break;

	case 'testing':
	case 'production':
		ini_set('display_errors', 0);
		if (version_compare(PHP_VERSION, '5.3', '>='))
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
		}
		else
		{
			error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
		}
	break;

	default:
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'The application environment is not set correctly.';
		exit(1); // EXIT_ERROR
}

/*
 *---------------------------------------------------------------
 * ASSET MODE
 * This sets the asset mode for css/js assets. 
 * Possible values are development (uncompressed) and production (compressed)
 *---------------------------------------------------------------
 * 
 *
 */
	define('ASSET_MODE', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'production');

	/*
	 *---------------------------------------------------------------
	 * LAZY LOAD
	 * Controls if models, libraries, and languages are lazy loaded if possible (Recommended to keep on)
	 *---------------------------------------------------------------
	 * 
	 *
	 */


	define('LAZY_LOAD', (!isset($_SERVER['CI_LAZY_LOAD']) || $_SERVER['CI_LAZY_LOAD']) ? TRUE : FALSE);
/*
|---------------------------------------------------------------
| AUTO DETECT LINE ENDINGS
|---------------------------------------------------------------
|
| Make sure that php tries to detect line endings. This is important
| when uploading .csv files created on the mac.
*/
ini_set('auto_detect_line_endings', true);


/*
|---------------------------------------------------------------
| AUTO DETECT LINE ENDINGS
|---------------------------------------------------------------
| Disable wsdl cache to prevent problems with Mercury
|
*/
ini_set("soap.wsdl_cache_enabled", 0);

/*
 *---------------------------------------------------------------
 * SYSTEM FOLDER NAME
 *---------------------------------------------------------------
 *
 * This variable must contain the name of your "system" directory.
 * Set the path if it is not in the same directory as this file.
 */
	$system_path = 'system';

/*
 *---------------------------------------------------------------
 * APPLICATION DIRECTORY NAME
 *---------------------------------------------------------------
 *
 * If you want this front controller to use a different "application"
 * directory than the default one you can set its name here. The directory
 * can also be renamed or relocated anywhere on your server. If you do,
 * use an absolute (full) server path.
 * For more info please see the user guide:
 *
 * https://codeigniter.com/user_guide/general/managing_apps.html
 *
 * NO TRAILING SLASH!
 */
	$application_folder = 'application';

/*
 *---------------------------------------------------------------
 * VIEW DIRECTORY NAME
 *---------------------------------------------------------------
 *
 * If you want to move the view directory out of the application
 * directory, set the path to it here. The directory can be renamed
 * and relocated anywhere on your server. If blank, it will default
 * to the standard location inside your application directory.
 * If you do move this, use an absolute (full) server path.
 *
 * NO TRAILING SLASH!
 */
	$view_folder = '';


/*
 * --------------------------------------------------------------------
 * DEFAULT CONTROLLER
 * --------------------------------------------------------------------
 *
 * Normally you will set your default controller in the routes.php file.
 * You can, however, force a custom routing by hard-coding a
 * specific controller class/function here. For most applications, you
 * WILL NOT set your routing here, but it's an option for those
 * special instances where you might want to override the standard
 * routing in a specific front controller that shares a common CI installation.
 *
 * IMPORTANT: If you set the routing here, NO OTHER controller will be
 * callable. In essence, this preference limits your application to ONE
 * specific controller. Leave the function name blank if you need
 * to call functions dynamically via the URI.
 *
 * Un-comment the $routing array below to use this feature
 */
	// The directory name, relative to the "controllers" directory.  Leave blank
	// if your controller is not in a sub-directory within the "controllers" one
	// $routing['directory'] = '';

	// The controller class file name.  Example:  mycontroller
	// $routing['controller'] = '';

	// The controller function you wish to be called.
	// $routing['function']	= '';


/*
 * -------------------------------------------------------------------
 *  CUSTOM CONFIG VALUES
 * -------------------------------------------------------------------
 *
 * The $assign_to_config array below will be passed dynamically to the
 * config class when initialized. This allows you to set custom config
 * items or override any default config values found in the config.php file.
 * This can be handy as it permits you to share one application between
 * multiple front controller files, with each file containing different
 * config values.
 *
 * Un-comment the $assign_to_config array below to use this feature
 */
	// $assign_to_config['name_of_config_item'] = 'value of config item';



// --------------------------------------------------------------------
// END OF USER CONFIGURABLE SETTINGS.  DO NOT EDIT BELOW THIS LINE
// --------------------------------------------------------------------

/*
 * ---------------------------------------------------------------
 *  Resolve the system path for increased reliability
 * ---------------------------------------------------------------
 */

	if (($_temp = realpath($system_path)) !== FALSE)
	{
		$system_path = $_temp.DIRECTORY_SEPARATOR;
	}
	else
	{
		// Ensure there's a trailing slash
		$system_path = strtr(
			rtrim($system_path, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		).DIRECTORY_SEPARATOR;
	}

	// Is the system path correct?
	if ( ! is_dir($system_path))
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your system folder path does not appear to be set correctly. Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME);
		exit(3); // EXIT_CONFIG
	}

/*
 * -------------------------------------------------------------------
 *  Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
	// The name of THIS file
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

	// Path to the system directory
	define('BASEPATH', $system_path);

	// Path to the front controller (this file) directory
	define('FCPATH', dirname(__FILE__).DIRECTORY_SEPARATOR);

	// Name of the "system" directory
	define('SYSDIR', basename(BASEPATH));

	// The path to the "application" directory
	if (is_dir($application_folder))
	{
		if (($_temp = realpath($application_folder)) !== FALSE)
		{
			$application_folder = $_temp;
		}
		else
		{
			$application_folder = strtr(
				rtrim($application_folder, '/\\'),
				'/\\',
				DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
			);
		}
	}
	elseif (is_dir(BASEPATH.$application_folder.DIRECTORY_SEPARATOR))
	{
		$application_folder = BASEPATH.strtr(
			trim($application_folder, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		);
	}
	else
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your application folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
		exit(3); // EXIT_CONFIG
	}

	define('APPPATH', $application_folder.DIRECTORY_SEPARATOR);

	// The path to the "views" directory
	if ( ! isset($view_folder[0]) && is_dir(APPPATH.'views'.DIRECTORY_SEPARATOR))
	{
		$view_folder = APPPATH.'views';
	}
	elseif (is_dir($view_folder))
	{
		if (($_temp = realpath($view_folder)) !== FALSE)
		{
			$view_folder = $_temp;
		}
		else
		{
			$view_folder = strtr(
				rtrim($view_folder, '/\\'),
				'/\\',
				DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
			);
		}
	}
	elseif (is_dir(APPPATH.$view_folder.DIRECTORY_SEPARATOR))
	{
		$view_folder = APPPATH.strtr(
			trim($view_folder, '/\\'),
			'/\\',
			DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
		);
	}
	else
	{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		echo 'Your view folder path does not appear to be set correctly. Please open the following file and correct this: '.SELF;
		exit(3); // EXIT_CONFIG
	}

	define('VIEWPATH', $view_folder.DIRECTORY_SEPARATOR);


//cli check() check for e-commerce cron. Ecommerce cron has to figure out which version it is on in case it needs to run cron on previous version of code
if (is_on_phppos_host() || (PHP_SAPI === 'cli' OR defined('STDIN')))
{

	include APPPATH.'config/database.php';
	$phppos_db_link = mysqli_connect($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);
	$migration_result = mysqli_query($phppos_db_link, "SELECT `version` FROM phppos_migrations");
	mysqli_close($phppos_db_link);
	if ($migration_result)
	{
		$db_migration_row = mysqli_fetch_assoc($migration_result);
		$db_migration_version = $db_migration_row['version'];
	}
	else
	{
		$db_migration_version = NULL;
	}
	
	$config = array('migration_version' => '0');
	include APPPATH.'config/migration.php';
	$app_migration_version = $config['migration_version'];
	if ($db_migration_version !== NULL && $db_migration_version!=$app_migration_version)
	{
		
		if (!empty($_SERVER) && isset($_SERVER['HTTP_HOST']))
		{
			$full_url = (is_https() ? 'https' : 'http').('://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		}
		else
		{
			$full_url = 'http://127.0.0.1';
		}
		$prev_folder = isset($_SERVER['CI_PREV_FOLDER']) ?  $_SERVER['CI_PREV_FOLDER'] : 'PHP-Point-Of-Sale-Prev';
		//We are on previous version; redirect to new version
		if (strpos($full_url, $prev_folder) !== FALSE)
		{
				require  APPPATH.'../../application/config/migration.php';
				$upgraded_app_migration_version = $config['migration_version'];
				
				if ($upgraded_app_migration_version == $db_migration_version)
				{
					define('SHOULD_BE_ON_NEW',true);
					$new_url = str_replace('/'.$prev_folder, '', $full_url);
				}
		}
		else //We are on new version redirect to old version
		{
			define('SHOULD_BE_ON_OLD',true);
			$url_insertion_point = strpos($full_url, 'index.php') !== FALSE ?  strpos($full_url, 'index.php') : strlen($full_url);
			$new_url = substr_replace($full_url, $prev_folder.'/', $url_insertion_point,0);
		}
		
		if (defined('SHOULD_BE_ON_NEW') || defined('SHOULD_BE_ON_OLD'))
		{
			if (!(PHP_SAPI === 'cli' OR defined('STDIN')))
			{
				header('HTTP/1.1 307 Temporary Redirect');
				header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
				header('Pragma: no-cache'); // HTTP 1.0.
				header('Expires: 0'); // Proxies.
				header("Location: $new_url",TRUE,307);
				exit();
			}
		}
	}
}
/*
 * --------------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * --------------------------------------------------------------------
 *
 * And away we go...
 */
require_once BASEPATH.'core/CodeIgniter.php';
