<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo\Model;

use Akeeba\Engine\Platform;
use Awf\Mvc\Model;

class Schedule extends Model
{
	public function getPaths()
	{
		$ret = (object) [
			'cli'      => (object) [
				'supported' => false,
				'path'      => false,
			],
			'altcli'   => (object) [
				'supported' => false,
				'path'      => false,
			],
			'frontend' => (object) [
				'supported' => false,
				'path'      => false,
			],
			'json'     => (object) [
				'supported' => false,
				'path'      => false,
			],
			'info'     => (object) [
				'windows'   => false,
				'php_path'  => false,
				'root_url'  => false,
				'jsonapi'   => false,
				'legacyapi' => false,
			],
		];

		$currentProfileID = Platform::getInstance()->get_active_profile();
		$siteRoot         = rtrim(realpath(APATH_BASE), DIRECTORY_SEPARATOR);

		$ret->info->windows   = (DIRECTORY_SEPARATOR == '\\') || (substr(strtoupper(PHP_OS), 0, 3) == 'WIN');
		$ret->info->php_path  = $ret->info->windows ? 'c:\path\to\php.exe' : '/path/to/php';
		$ret->info->root_url  = rtrim(Platform::getInstance()->get_platform_configuration_option('siteurl', ''), '/');
		$ret->info->secret    = Platform::getInstance()->get_platform_configuration_option('frontend_secret_word', '');
		$ret->info->jsonapi   = Platform::getInstance()->get_platform_configuration_option('jsonapi_enabled', '');
		$ret->info->legacyapi = Platform::getInstance()->get_platform_configuration_option('legacyapi_enabled', '');

		// Get information for CLI CRON script
		$ret->cli->supported = true;
		$ret->cli->path      = $siteRoot . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'backup.php';

		if ($currentProfileID != 1)
		{
			$ret->cli->path .= ' --profile=' . $currentProfileID;
		}

		// Get information for alternative CLI CRON script
		$ret->altcli->supported = $ret->info->legacyapi;

		if (trim($ret->info->secret) && $ret->info->legacyapi)
		{
			$ret->altcli->path = $siteRoot . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'altbackup.php';

			if ($currentProfileID != 1)
			{
				$ret->altcli->path .= ' --profile=' . $currentProfileID;
			}
		}

		// Get information for front-end backup
		$ret->frontend->supported = $ret->info->legacyapi;

		if (trim($ret->info->secret) && $ret->info->legacyapi)
		{
			$ret->frontend->path = defined('WPINC')
				? admin_url('admin-ajax.php?action=akeebabackup_legacy')
				: 'index.php?view=remote';

			$ret->frontend->path .= "&key=" . urlencode($ret->info->secret);

			if ($currentProfileID != 1)
			{
				$ret->frontend->path .= '&profile=' . $currentProfileID;
			}
		}

		// Get information for JSON API backups
		$ret->json->supported = $ret->info->jsonapi;
		$ret->json->path      = defined('WPINC')
			? admin_url('admin-ajax.php?action=akeebabackup_api')
			:'index.php?view=api';

		return $ret;
	}

	public function getCheckPaths()
	{
		$ret = (object) [
			'cli'      => (object) [
				'supported' => false,
				'path'      => false,
			],
			'altcli'   => (object) [
				'supported' => false,
				'path'      => false,
			],
			'frontend' => (object) [
				'supported' => false,
				'path'      => false,
			],
			'info'     => (object) [
				'windows'   => false,
				'php_path'  => false,
				'root_url'  => false,
				'secret'    => '',
				'legacyapi' => false,
			],
		];

		$siteRoot = rtrim(realpath(APATH_BASE), DIRECTORY_SEPARATOR);

		$ret->info->windows   = (DIRECTORY_SEPARATOR == '\\') || (substr(strtoupper(PHP_OS), 0, 3) == 'WIN');
		$ret->info->php_path  = $ret->info->windows ? 'c:\path\to\php.exe' : '/path/to/php';
		$ret->info->root_url  = rtrim(Platform::getInstance()->get_platform_configuration_option('siteurl', ''), '/');
		$ret->info->secret    = Platform::getInstance()->get_platform_configuration_option('frontend_secret_word', '');
		$ret->info->legacyapi = Platform::getInstance()->get_platform_configuration_option('legacyapi_enabled', '');

		// Get information for CLI CRON script
		$ret->cli->supported = true;
		$ret->cli->path      = $siteRoot . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'check-failed.php';

		// Get information for alternative CLI CRON script
		$ret->altcli->supported = true;
		if (trim($ret->info->secret) && $ret->info->legacyapi)
		{
			$ret->altcli->path = $siteRoot . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'altcheck-failed.php';
		}

		// Get information for front-end backup
		$ret->frontend->supported = $ret->info->legacyapi;

		if (trim($ret->info->secret) && $ret->info->legacyapi)
		{
			$ret->frontend->path = "index.php?view=check&key=" . urlencode($ret->info->secret);
		}

		return $ret;
	}
} 
