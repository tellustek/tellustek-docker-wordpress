<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\WPCLI\Command;

use Akeeba\Engine\Factory;
use Awf\Mvc\Model;
use Solo\Application;
use Solo\Model\Log as LogModel;
use WP_CLI;
use WP_CLI\Utils as CliUtils;

/**
 * Access the Akeeba Backup log files.
 *
 * @package     Akeeba\WPCLI\Command
 *
 * @since       3.0.0
 */
class Log
{
	/**
	 * Lists the Akeeba Backup log files currently on the server
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : The format for the returned list
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - json
	 *   - csv
	 *   - yaml
	 *   - count
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp akeeba log list
	 *
	 *     wp akeeba log list --format=json
	 *
	 * @when       after_wp_load
	 * @subcommand list
	 *
	 * @param   array $args       Positional arguments (literal arguments)
	 * @param   array $assoc_args Associative arguments (--flag, --no-flag, --key=value)
	 *
	 * @return  void
	 *
	 * @throws  WP_CLI\ExitException
	 *
	 * @since       3.0.0
	 */
	public function _list($args, $assoc_args)
	{
		/** @var  Application $akeebaBackupApplication */
		global $akeebaBackupApplication;

		// Don't remove; used to autoload AWF's array helpers which we need below.
		class_exists('Awf\\Utils\\Collection');

		$format    = isset($assoc_args['format']) ? $assoc_args['format'] : 'table';
		$container = $akeebaBackupApplication->getContainer();

		/** @var \Solo\Model\Log $model */
		$model   = Model::getTmpInstance($container->application_name, 'Log', $container);
		$logList = $model->getLogFiles();
		$logList = array_values($logList);
		$i       = 0;
		$logList = akeeba_array_build($logList, function ($key, $value) use (&$i) {
			return [$i++, ['id' => $value]];
		});

		CliUtils\format_items($format, $logList, ['id']);
	}

	/**
	 * Retrieves the Akeeba Backup log file for the given tag
	 *
	 * ## OPTIONS
	 *
	 * <id>
	 * : The log file ID, as returned by wp akeeba log list
	 *
	 * ## EXAMPLES
	 *
	 *     wp akeeba log get backend
	 *
	 *     wp akeeba log get frontend.id123
	 *
	 * @when       after_wp_load
	 *
	 * @param   array $args       Positional arguments (literal arguments)
	 * @param   array $assoc_args Associative arguments (--flag, --no-flag, --key=value)
	 *
	 * @return  void
	 *
	 * @throws  WP_CLI\ExitException
	 *
	 * @since       3.0.0
	 */
	public function get($args, $assoc_args)
	{
		if (!isset($args[0]))
		{
			WP_CLI::error("You must specify the log ID to retrieve.");
		}

		$container = \Awf\Application\Application::getInstance()->getContainer();
		/** @var LogModel $model */
		$model = Model::getTmpInstance($container->application_name, 'Log', $container);
		$model->setState('tag', $args[0]);
		$model->echoRawLog(false);
	}
}
