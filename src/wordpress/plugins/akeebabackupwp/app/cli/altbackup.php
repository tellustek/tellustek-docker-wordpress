<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo;

use Awf\Autoloader\Autoloader;
use Awf\Text\Text;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;

// Should I enable debug?
// define('AKEEBADEBUG', 1);

if (defined('AKEEBADEBUG'))
{
	define('AKEEBADEBUG_ERROR_DISPLAY', 1);
}

if (defined('AKEEBADEBUG') && defined('AKEEBADEBUG_ERROR_DISPLAY'))
{
	error_reporting(E_ALL | E_NOTICE | E_DEPRECATED);
	ini_set('display_errors', 1);
}

$minphp = '7.2.0';
if (version_compare(PHP_VERSION, $minphp, 'lt'))
{
	$curversion = PHP_VERSION;
	$bindir     = PHP_BINDIR;
	echo <<< ENDWARNING
================================================================================
WARNING! Incompatible PHP version $curversion
================================================================================

This CRON script must be run using PHP version $minphp or later. Your server is
currently using a much older version which would cause this script to crash. As
a result we have aborted execution of the script. Please contact your host and
ask them for the correct path to the PHP CLI binary for PHP $minphp or later, then
edit your CRON job and replace your current path to PHP with the one your host
gave you.

For your information, the current PHP version information is as follows.

PATH:    $bindir
VERSION: $curversion

Further clarifications:

1. There is absolutely no possible way that you are receiving this warning in
   error. We are using the PHP_VERSION constant to detect the PHP version you
   are currently using. This is what PHP itself reports as its own version.

2. Even though your *site* may be running in a higher PHP version that the one
   reported above, your CRON scripts will most likely not be running under it.
   This has to do with the fact that your site DOES NOT run under the command
   line and there are different executable files (binaries) for the web and
   command line versions of PHP.

3. Please note that you should not ask us for support about this error. We
   cannot possibly know the correct path to the PHP CLI binary as we have not
   set up your server. Your host does know and can give you that information.

4. The latest published versions of PHP can be found at http://www.php.net/
   Any older version is considered insecure and must not be used on a live
   server. If your server uses a much older version of PHP than that please
   notify your host that their servers are in need of an update.

The execution of this command line script is aborted.

ENDWARNING;
	exit(255);
}

// Timezone fix; avoids errors printed out by PHP 5.3.3+ (thanks Yannick!)
if (function_exists('date_default_timezone_get') && function_exists('date_default_timezone_set'))
{
	if (function_exists('error_reporting'))
	{
		$oldLevel = error_reporting(0);
	}
	$serverTimezone = @date_default_timezone_get();
	if (empty($serverTimezone) || !is_string($serverTimezone))
	{
		$serverTimezone = 'UTC';
	}
	if (function_exists('error_reporting'))
	{
		error_reporting($oldLevel);
	}
	@date_default_timezone_set($serverTimezone);
}

// Include the Composer autoloader
if (false === include __DIR__ . '/../vendor/autoload.php')
{
	echo 'ERROR: Composer Autoloader not found' . PHP_EOL;

	exit(1);
}

// Include the AWF autoloader
if (false === include __DIR__ . '/../Awf/Autoloader/Autoloader.php')
{
	echo 'ERROR: AWF Autoloader not found' . PHP_EOL;

	exit(1);
}

// Load the integration script
defined('AKEEBASOLO') || define('AKEEBASOLO', 1);
$dirParts = explode(DIRECTORY_SEPARATOR, $argv[0]);

if (!isset($myDir) && count($dirParts) > 3)
{
	$dirParts = array_splice($dirParts, 0, -3);
	$myDir    = implode(DIRECTORY_SEPARATOR, $dirParts);
}

if (
	isset($cliOverrides) && is_array($cliOverrides) && isset($cliOverrides['__abwpRoot'])
	&& @file_exists($cliOverrides['__abwpRoot'] . '/helpers/integration.php')
)
{
	$root = $cliOverrides['__abwpRoot'];
	unset($cliOverrides['__abwpRoot']);
	$container = require $root . '/helpers/integration.php';
}
elseif (@file_exists(__DIR__ . '/../../helpers/integration.php'))
{
	$container = require __DIR__ . '/../../helpers/integration.php';
}
elseif (@file_exists('../../helpers/integration.php'))
{
	$container = require '../../helpers/integration.php';
}
elseif (isset($myDir) && @file_exists($myDir . '/helpers/integration.php'))
{
	$container = require $myDir . '/helpers/integration.php';
}

// Load the platform defines
if (!defined('APATH_BASE'))
{
	require_once __DIR__ . '/../defines.php';
}

// Load the version file
if (@file_exists(__DIR__ . '/../version.php'))
{
	require_once __DIR__ . '/../version.php';
}

// Add our app to the autoloader, if it's not already set
$prefixes = Autoloader::getInstance()->getPrefixes();
if (!array_key_exists('Solo\\', $prefixes))
{
	Autoloader::getInstance()->addMap('Solo\\', APATH_BASE . '/Solo');
}

// Include the Akeeba Engine factory
if (!defined('AKEEBAENGINE'))
{
	define('AKEEBAENGINE', 1);
	$factoryPath = __DIR__ . '/../Solo/engine/Factory.php';

	// Load the engine
	if (!file_exists($factoryPath))
	{
		echo "ERROR!\n";
		echo "Could not load the backup engine; file does not exist. Technical information:\n";
		echo "Path to " . basename(__FILE__) . ": " . __DIR__ . "\n";
		echo "Path to factory file: $factoryPath\n";
		die("\n");
	}
	else
	{
		try
		{
			require_once $factoryPath;
		}
		catch (\Exception $e)
		{
			echo "ERROR!\n";
			echo "Backup engine returned an error. Technical information:\n";
			echo "Error message:\n\n";
			echo $e->getMessage() . "\n\n";
			echo "Path to " . basename(__FILE__) . ":" . __DIR__ . "\n";
			echo "Path to factory file: $factoryPath\n";
			die("\n");
		}
	}

	Platform::addPlatform('Solo', __DIR__ . '/../Solo/Platform/Solo');
	Platform::getInstance()->load_version_defines();
	Platform::getInstance()->apply_quirk_definitions();
}

// Tell the Akeeba Engine where to find a valid cacert.pem file
defined('AKEEBA_CACERT_PEM') || define('AKEEBA_CACERT_PEM', \Composer\CaBundle\CaBundle::getBundledCaBundlePath());

class BackupApplication extends \Awf\Application\Cli
{
	/**
	 * When making HTTPS connections, should we verify the certificate validity and that the hostname matches the one
	 * in the certificate? Turned on by default. You can disable with the --no-verify CLI option.
	 *
	 * @var  bool
	 */
	private $verifySSL = true;

	/**
	 * Path to the engine's settings encryption key
	 */
	const secretKeyRelativePath = '/engine/secretkey.php';

	public function __construct(\Awf\Container\Container $container = null)
	{
		parent::__construct($container);

		if (empty($this->container->basePath))
		{
			$this->container->basePath = APATH_BASE . '/Solo';
		}
	}

	public function initialise()
	{
		// Load the extra language files
		Text::loadLanguage('en-GB', 'akeeba', '.com_akeeba.ini', true, $this->container->languagePath);
		Text::loadLanguage('en-GB', 'akeebabackup', '.com_akeebabackup.ini', true, $this->container->languagePath);
		Text::loadLanguage(null, 'akeeba', '.com_akeeba.ini', true, $this->container->languagePath);
		Text::loadLanguage(null, 'akeebabackup', '.com_akeebabackup.ini', true, $this->container->languagePath);

		// Halt if the configuration does not exist yet
		$configPath   = $this->getContainer()->appConfig->getDefaultPath();
		$isConfigured = @file_exists($configPath) || (defined('DB_NAME') && defined('DB_USER') && defined('DB_PASSWORD') && defined('DB_HOST'));

		if (!$isConfigured)
		{
			$this->out('Configuration not found; aborting');
			$this->close(254);
		}

		// Load the application's configuration
		$this->container->appConfig->loadConfiguration($configPath);

		// Load Akeeba Engine's settings encryption preferences
		$secretKeyFile = $this->getContainer()->basePath . static::secretKeyRelativePath;

		if (@file_exists($secretKeyFile))
		{
			require_once $secretKeyFile;
		}

		// Load Akeeba Engine's configuration
		Platform::getInstance()->load_configuration();

		return $this;
	}

	/**
	 * Language file processing callback. It converts _QQ_ to " and replaces the product name in the legacy INI files
	 * imported from Akeeba Backup for Joomla!.
	 *
	 * @param   string  $filename  The full path to the file being loaded
	 * @param   array   $strings   The key/value array of the translations
	 *
	 * @return  boolean|array  False to prevent loading the file, or array of processed language string, or true to
	 *                         ignore this processing callback.
	 */
	public function processLanguageIniFile($filename, $strings)
	{
		foreach ($strings as $k => $v)
		{
			$v           = str_replace('_QQ_', '"', $v);
			$v           = str_replace('Akeeba Backup', 'Akeeba Solo', $v);
			$strings[$k] = $v;
		}

		return $strings;
	}

	/**
	 * Method to run the application routines.  Most likely you will want to instantiate a controller
	 * and execute it, or perform some sort of task directly.
	 *
	 * @return  void
	 */
	protected function doExecute()
	{
		// Has the user set the --no-verify option?
		$noVerifyValue   = $this->getContainer()->input->get('no-verify', 999);
		$this->verifySSL = $noVerifyValue != 999;

		// Get the backup profile and description
		$profile = $this->getContainer()->input->get('profile', 1, 'int');

		if ($profile <= 0)
		{
			$profile = 1;
		}

		$debugmessage = '';

		if ($this->getContainer()->input->get('debug', -1, 'int') != -1)
		{
			if (!defined('AKEEBADEBUG'))
			{
				define('AKEEBADEBUG', 1);
			}

			$debugmessage = "*** DEBUG MODE ENABLED ***\n";
		}

		$version      = AKEEBABACKUP_VERSION;
		$date         = AKEEBABACKUP_DATE;
		$start_backup = time();
		$memusage     = $this->memUsage();

		$phpversion     = PHP_VERSION;
		$phpenvironment = PHP_SAPI;
		$phpos          = PHP_OS;

		$appName = defined('ABSPATH') ? 'Akeeba backup' : 'Akeeba Solo';

		if ($this->getContainer()->input->get('quiet', -1, 'int') == -1)
		{
			$year = gmdate('Y');
			echo <<<ENDBLOCK
$appName Alternate CLI Backup Script version $version ($date)
Copyright (C) 2014-$year Nicholas K. Dionysopoulos / Akeeba Ltd
-------------------------------------------------------------------------------
$appName is Free Software, distributed under the terms of the GNU General
Public License version 3 or, at your option, any later version.
This program comes with ABSOLUTELY NO WARRANTY as per sections 15 & 16 of the
license. See http://www.gnu.org/licenses/gpl-3.0.html for details.
-------------------------------------------------------------------------------
You are using PHP $phpversion ($phpenvironment)
$debugmessage
Current memory usage: $memusage


ENDBLOCK;
		}

		// Attempt to use an infinite time limit, in case you are using the PHP CGI binary instead
		// of the PHP CLI binary. This will not work with Safe Mode, though.
		$safe_mode = true;

		if (function_exists('ini_get'))
		{
			$safe_mode = ini_get('safe_mode');
		}

		if (!$safe_mode && function_exists('set_time_limit'))
		{
			if ($this->getContainer()->input->get('quiet', -1, 'int') == -1)
			{
				echo "Unsetting time limit restrictions.\n";
			}

			@set_time_limit(0);
		}
		elseif (!$safe_mode)
		{
			if ($this->getContainer()->input->get('quiet', -1, 'int') == -1)
			{
				echo "Could not unset time limit restrictions; you may get a timeout error\n";
			}
		}
		else
		{
			if ($this->getContainer()->input->get('quiet', -1, 'int') == -1)
			{
				echo "You are using PHP's Safe Mode; you may get a timeout error\n";
			}
		}

		if ($this->getContainer()->input->get('quiet', -1, 'int') == -1)
		{
			echo "\n";
		}

		// Log some paths
		if ($this->getContainer()->input->get('quiet', -1, 'int') == -1)
		{
			echo "Site paths determined by this script:\n";
			echo "APATH_BASE : " . APATH_BASE . "\n";
		}

		$startup_check = true;

		$url = Platform::getInstance()->get_platform_configuration_option('siteurl', '');

		if (empty($url))
		{
			echo <<<ENDTEXT
ERROR:
	This script could not detect your $appName installation's URL. Please
	visit {$appName}'s main page at least once before running this script.
	When you do that, $appName will record the URL to itself and make it
	available to this script.

ENDTEXT;
			$startup_check = false;
		}

		// Get the front-end backup settings
		$frontend_enabled = Platform::getInstance()->get_platform_configuration_option('legacyapi_enabled', '');
		$secret           = Platform::getInstance()->get_platform_configuration_option('frontend_secret_word', '');

		if (!$frontend_enabled)
		{
			echo <<<ENDTEXT
ERROR:
	Your $appName installation's Legacy Front-end API feature is currently
	disabled. Please log in to $appName, click on the System Configuration
	icon in the system management pane towards the bottom of the page and
	set Enable Legacy Front-end Backup API to Yes. Do not forget to also set 
	a Secret Word!

ENDTEXT;
			$startup_check = false;
		}
		elseif (empty($secret))
		{
			echo <<<ENDTEXT
ERROR:
	You have set Enable Legacy Front-end API to Yes, but you forgot to set a
	Secret Word. This script can not continue with an empty Secret Word.
	Please log in to $appName, click on the System Configuration
	icon in the system management pane towards the bottom of the page and
	set a Secret Word.

ENDTEXT;
			$startup_check = false;
		}

		// Detect cURL or fopen URL
		$method = null;
		if (function_exists('curl_init'))
		{
			$method = 'curl';
		}
		elseif (function_exists('fsockopen'))
		{
			$method = 'fsockopen';
		}

		if (empty($method))
		{
			if (function_exists('ini_get'))
			{
				if (ini_get('allow_url_fopen'))
				{
					$method = 'fopen';
				}
			}
		}

		$overridemethod = $this->getContainer()->input->get('method', '', 'cmd');

		if (!empty($overridemethod))
		{
			$method = $overridemethod;
		}

		if (empty($method))
		{
			echo <<<ENDTEXT
ERROR:
	Could not find any supported method for running the front-end backup
	feature of $appName. Please check with your host that at least
	one of the following features are supported in your PHP configuration:

	1. The cURL extension
	2. The fsockopen() function
	3. The fopen() URL wrappers, i.e. allow_url_fopen is enabled

	If neither method is available you will not be able to run a backup using
	this CRON helper script.

ENDTEXT;
			$startup_check = false;
		}

		if (!$startup_check)
		{
			echo "\n\nBACKUP ABORTED DUE TO CONFIGURATION ERRORS\n\n";
			$this->close(255);
		}

		echo <<<ENDBLOCK
Starting a new backup with the following parameters:
Profile ID    : $profile
Backup Method : $method


ENDBLOCK;

		// Perform the backup
		$url    = rtrim($url, '/');
		$secret = urlencode($secret);

		if (defined('ABSPATH'))
		{
			$tempURL = Platform::getInstance()->get_platform_configuration_option('ajaxurl', '');
			$url     = empty($tempURL) ? ($url . '/wp-admin/admin-ajax.php') : $tempURL;
		}
		else
		{
			$url .= '/index.php';
		}

		$url       .= defined('ABSPATH') ? '?action=akeebabackup_legacy' : '?view=remote';
		$url       .= "&key={$secret}&noredirect=1&profile=$profile";
		$old_url   = $url;
		$inLoop    = true;
		$step      = 0;
		$timestamp = date('Y-m-d H:i:s');
		echo "[{$timestamp}] Beginning backing up\n";

		while ($inLoop)
		{
			$timestamp = date('Y-m-d H:i:s');

			$result = $this->fetchURL($url, $method);

			echo "[{$timestamp}] Got $result\n";

			if (empty($result) || ($result === false))
			{
				echo "[{$timestamp}] No message received\n";
				echo <<<ENDTEXT
ERROR:
	Your backup attempt has timed out, or a fatal PHP error has occurred.
	Please check the backup log and your server's error log for more
	information.

Backup failed.

ENDTEXT;
				$this->close(100);

				$inLoop = false;
			}
			elseif (strpos($result, '301 More work required') !== false)
			{
				// Extract the backup ID
				$backupId = null;
				$startPos = strpos($result, 'BACKUPID #"\#\"#');
				$endPos   = false;

				if ($startPos !== false)
				{
					$endPos = strpos($result, '#"\#\"#', $startPos + 15);
				}

				if ($endPos !== false)
				{
					$backupId = substr($result, $startPos + 16, $endPos - $startPos - 16);
				}

				// Construct the new URL and access it

				if ($step == 0)
				{
					$old_url = $url;
				}

				$step++;
				$url = $old_url . '&task=step&step=' . $step;

				if (!is_null($backupId))
				{
					$url .= '&backupid=' . urlencode($backupId);
				}

				echo "[{$timestamp}] Backup progress signal received\n";
			}
			elseif (strpos($result, '200 OK') !== false)
			{
				echo "[{$timestamp}] Backup finalization message received\n";
				echo <<<ENDTEXT

Your backup has finished successfully.

Please review your backup log file for any warning messages. If you see any
such messages, please make sure that your backup is working properly by trying
to restore it on a local server.

ENDTEXT;
				$inLoop = false;

				echo "Backup job finished after approximately " . $this->timeago($start_backup, time(), '', false) . "\n";
				echo "Peak memory usage: " . $this->peakMemUsage() . "\n\n";

				$this->close(0);
			}
			elseif (strpos($result, '500 ERROR -- ') !== false)
			{
				// Backup error
				echo "[{$timestamp}] Error signal received\n";
				echo <<<ENDTEXT
ERROR:
	A backup error has occurred. The server's response was:

$result

Backup failed.

ENDTEXT;
				$inLoop = false;

				$this->close(2);
			}
			elseif (strpos($result, '403 ') !== false)
			{
				// This should never happen: invalid authentication or front-end backup disabled
				echo "[{$timestamp}] Connection denied (403) message received\n";
				echo <<<ENDTEXT
ERROR:
	The server denied the connection. Please make sure that Enable Legacy
	Front-end API is set to Yes and a valid secret word is in place.

	Server response: $result

Backup failed.

ENDTEXT;
				$inLoop = false;

				$this->close(103);
			}
			else
			{
				// Unknown result?!
				echo "[{$timestamp}] Could not parse the server response.\n";
				echo <<<ENDTEXT
ERROR:
	We could not understand the server's response. Most likely a backup error
	has occurred. The server's response was:

$result

	If you do not see "200 OK" at the end of this output, the backup has
	failed.

ENDTEXT;
				$inLoop = false;

				$this->close(1);
			}
		}
	}

	/**
	 * Fetches a remote URL using curl, fsockopen or fopen
	 *
	 * @param   string  $url     The remote URL to fetch
	 * @param   string  $method  The method to use: curl, fsockopen or fopen (optional)
	 *
	 * @return string The contents of the URL which was fetched
	 */
	private function fetchURL($url, $method = 'curl')
	{
		switch ($method)
		{
			default:
			case 'curl':
				$ch         = curl_init($url);
				$cacertPath = APATH_BASE . '/Awf/Download/Adapter/cacert.pem';

				if (file_exists($cacertPath))
				{
					@curl_setopt($ch, CURLOPT_CAINFO, $cacertPath);
				}
				@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				@curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
				@curl_setopt($ch, CURLOPT_HEADER, false);
				@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verifySSL);
				@curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verifySSL ? 2 : 0);
				@curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 180);
				@curl_setopt($ch, CURLOPT_TIMEOUT, 180);
				$result = curl_exec($ch);
				curl_close($ch);

				return $result;
				break;

			case 'fsockopen':
				$pos      = strpos($url, '://');
				$protocol = strtolower(substr($url, 0, $pos));
				$req      = substr($url, $pos + 3);
				$pos      = strpos($req, '/');
				if ($pos === false)
				{
					$pos = strlen($req);
				}
				$host = substr($req, 0, $pos);

				if (strpos($host, ':') !== false)
				{
					[$host, $port] = explode(':', $host);
				}
				else
				{
					$port = ($protocol == 'https') ? 443 : 80;
				}

				$uri = substr($req, $pos);
				if ($uri == '')
				{
					$uri = '/';
				}

				$crlf = "\r\n";
				$req  = 'GET ' . $uri . ' HTTP/1.0' . $crlf
					. 'Host: ' . $host . $crlf
					. $crlf;

				$fp = fsockopen(($protocol == 'https' ? 'ssl://' : '') . $host, $port);
				fwrite($fp, $req);
				$response = '';
				while (is_resource($fp) && $fp && !feof($fp))
				{
					$response .= fread($fp, 1024);
				}
				fclose($fp);

				// split header and body
				$pos = strpos($response, $crlf . $crlf);
				if ($pos === false)
				{
					return ($response);
				}
				$header = substr($response, 0, $pos);
				$body   = substr($response, $pos + 2 * strlen($crlf));

				// parse headers
				$headers = [];
				$lines   = explode($crlf, $header);
				foreach ($lines as $line)
				{
					if (($pos = strpos($line, ':')) !== false)
					{
						$headers[strtolower(trim(substr($line, 0, $pos)))] = trim(substr($line, $pos + 1));
					}
				}

				//redirection?
				if (isset($headers['location']))
				{
					return $this->fetchURL($headers['location'], $method);
				}
				else
				{
					return ($body);
				}

				break;

			case 'fopen':
				$opts = [
					'http' => [
						'method' => "GET",
						'header' => "Accept-language: en\r\n",
					],
				];

				$context = stream_context_create($opts);
				$result  = @file_get_contents($url, false, $context);
				break;
		}

		return $result;
	}

	/**
	 * Returns a fancy formatted time lapse code
	 *
	 * @param   integer         $referenceDateTime  Timestamp of the reference date/time
	 * @param   string|integer  $currentDateTime    Timestamp of the current date/time
	 * @param   string          $measureBy          One of s, m, h, d, or y (time unit)
	 * @param   boolean         $autoText           Append text automatically?
	 *
	 * @return  string
	 */
	private function timeago($referenceDateTime = 0, $currentDateTime = '', $measureBy = '', $autoText = true)
	{
		if ($currentDateTime == '')
		{
			$currentDateTime = time();
		}

		// Raw time difference
		$Raw   = $currentDateTime - $referenceDateTime;
		$Clean = abs($Raw);

		$calcNum = [
			['s', 60],
			['m', 60 * 60],
			['h', 60 * 60 * 60],
			['d', 60 * 60 * 60 * 24],
			['y', 60 * 60 * 60 * 24 * 365],
		];

		$calc = [
			's' => [1, 'second'],
			'm' => [60, 'minute'],
			'h' => [60 * 60, 'hour'],
			'd' => [60 * 60 * 24, 'day'],
			'y' => [60 * 60 * 24 * 365, 'year'],
		];

		if ($measureBy == '')
		{
			$usemeasure = 's';

			for ($i = 0; $i < count($calcNum); $i++)
			{
				if ($Clean <= $calcNum[$i][1])
				{
					$usemeasure = $calcNum[$i][0];
					$i          = count($calcNum);
				}
			}
		}
		else
		{
			$usemeasure = $measureBy;
		}

		$datedifference = floor($Clean / $calc[$usemeasure][0]);

		if ($autoText == true && ($currentDateTime == time()))
		{
			if ($Raw < 0)
			{
				$prospect = ' from now';
			}
			else
			{
				$prospect = ' ago';
			}
		}
		else
		{
			$prospect = '';
		}

		if ($referenceDateTime != 0)
		{
			if ($datedifference == 1)
			{
				return $datedifference . ' ' . $calc[$usemeasure][1] . ' ' . $prospect;
			}
			else
			{
				return $datedifference . ' ' . $calc[$usemeasure][1] . 's ' . $prospect;
			}
		}
		else
		{
			return 'No input time referenced.';
		}
	}

	/**
	 * Returns the current memory usage
	 *
	 * @return string
	 */
	private function memUsage()
	{
		if (function_exists('memory_get_usage'))
		{
			$size = memory_get_usage();
			$unit = ['b', 'KB', 'MB', 'GB', 'TB', 'PB'];

			return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
		}
		else
		{
			return "(unknown)";
		}
	}

	/**
	 * Returns the peak memory usage
	 *
	 * @return string
	 */
	private function peakMemUsage()
	{
		if (function_exists('memory_get_peak_usage'))
		{
			$size = memory_get_peak_usage();
			$unit = ['b', 'KB', 'MB', 'GB', 'TB', 'PB'];

			return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
		}
		else
		{
			return "(unknown)";
		}
	}

	/**
	 * Parses POSIX command line options and returns them as an associative array. Each array item contains
	 * a single dimensional array of values. Arguments without a dash are silently ignored.
	 *
	 * @return array
	 */
	private function parseOptions()
	{
		global $argc, $argv;

		// Workaround for PHP-CGI
		if (!isset($argc) && !isset($argv))
		{
			$query = "";

			if (!empty($_GET))
			{
				foreach ($_GET as $k => $v)
				{
					$query .= " $k";

					if ($v != "")
					{
						$query .= "=$v";
					}
				}
			}
			$query = ltrim($query);
			$argv  = explode(' ', $query);
			$argc  = count($argv);
		}

		$currentName = "";
		$options     = [];

		for ($i = 1; $i < $argc; $i++)
		{
			$argument = $argv[$i];

			if (strpos($argument, "-") === 0)
			{
				$argument = ltrim($argument, '-');

				if (strstr($argument, '='))
				{
					[$name, $value] = explode('=', $argument, 2);
				}
				else
				{
					$name  = $argument;
					$value = null;
				}

				$currentName = $name;

				if (!isset($options[$currentName]) || ($options[$currentName] == null))
				{
					$options[$currentName] = [];
				}
			}
			else
			{
				$value = $argument;
			}
			if ((!is_null($value)) && (!is_null($currentName)))
			{
				if (strstr($value, '='))
				{
					$parts = explode('=', $value, 2);
					$key   = $parts[0];
					$value = $parts[1];
				}
				else
				{
					$key = null;
				}

				$values = $options[$currentName];

				if (is_null($values))
				{
					$values = [];
				}

				if (is_null($key))
				{
					array_push($values, $value);
				}
				else
				{
					$values[$key] = $value;
				}

				$options[$currentName] = $values;
			}
		}

		return $options;
	}

	/**
	 * Returns the value of a command line option
	 *
	 * @param   string   $key              The full name of the option, e.g. "foobar"
	 * @param   mixed    $default          The default value to return
	 * @param   boolean  $first_item_only  Return only the first value specified (default = true)
	 *
	 * @return  mixed
	 */
	private function getOption($key, $default = null, $first_item_only = true)
	{
		static $options = null;

		if (is_null($options))
		{
			$options = $this->parseOptions();
		}

		if (!array_key_exists($key, $options))
		{
			return $default;
		}
		else
		{
			if ($first_item_only)
			{
				return $options[$key][0];
			}
			else
			{
				return $options[$key];
			}
		}
	}
}

if (!isset($container))
{
	$container = new \Solo\Container();
}

$app = new BackupApplication($container);

// Allow overrides for OVH braindead CRON jobs.
if (isset($cliOverrides) && is_array($cliOverrides) && !empty($cliOverrides))
{
	foreach ($cliOverrides as $key => $value)
	{
		$container->input->set($key, $value);
	}
}

$app->initialise()->execute();
