<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\Params;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Library\Mvc\Model\Model;
use Akeeba\Engine\Platform;


class SchedulingInformation extends Model
{
	public function getPaths()
	{
		$ret = (object) array(
			'cli'      => (object) array(
				'supported' => false,
				'path'      => false
			),
			'altcli'   => (object) array(
				'supported' => false,
				'path'      => false
			),
			'frontend' => (object) array(
				'supported' => false,
				'path'      => false,
			),
			'info'     => (object) array(
				'windows'   => false,
				'php_path'  => false,
				'root_url'  => false,
				'secret'    => '',
				'feenabled' => false,
			)
		);

		// Is this Windows?
		$ret->info->windows = (DIRECTORY_SEPARATOR == '\\') || (substr(strtoupper(PHP_OS), 0, 3) == 'WIN');

		// Get the pseudo-path to PHP CLI
		if ($ret->info->windows)
		{
			$ret->info->php_path = 'c:\path\to\php.exe';
		}
		else
		{
			$ret->info->php_path = '/path/to/php';
		}
		// Get front-end backup secret key
		$params = Params::getInstance();
		$ret->info->secret    = $params->getValue('frontend_secret_word', '');
		$ret->info->feenabled = $params->getValue('frontend_enable', false);

		// Get root URL
		$ret->info->root_url = rtrim(Wordpress::get_option('siteurl', ''), '/');

		// Get information for CLI CRON script
		$ret->cli->supported = true;
		$ret->cli->path      = ADMINTOOLSWP_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'admintools-filescanner.php';

		// Get information for alternative CLI CRON script
		$ret->altcli->supported = true;

		if (trim($ret->info->secret) && $ret->info->feenabled)
		{
			$ret->altcli->path = ADMINTOOLSWP_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'admintools-filescanner-alt.php';
		}

		// Get information for front-end backup
		$ret->frontend->supported = true;
		if (trim($ret->info->secret) && $ret->info->feenabled)
		{
			$ret->frontend->path = 'wp-content/plugins/admintoolswp/filescanner.php?key=' . urlencode($ret->info->secret);
		}

		return $ret;
	}
}
