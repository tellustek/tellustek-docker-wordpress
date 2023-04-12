<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\WPCLI\Command;

use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use Awf\Mvc\Model;
use Solo\Application;
use WP_CLI;

/**
 * This file contains the Pro version-only features. The Core version does not include it and, as a result, cannot use
 * these features.
 *
 * Class ProFeatures
 *
 * @package Akeeba\WPCLI\Command
 */
class ProFeatures
{
	/**
	 * Takes a backup with Akeeba Backup. WARNING! Do NOT use with the --http=<http> option of WP-CLI, it will NOT work on most sites.
	 *
	 * ## OPTIONS
	 *
	 * [--profile=<profile>]
	 * : Take a backup using the given profile ID, uses profile #1 if not specified
	 *
	 * [--description=<description>]
	 * : Apply this backup description, accepts the standard Akeeba Backup archive naming variables
	 *
	 * [--comment=<comment>]
	 * : Use this backup comment, provide it in HTML
	 *
	 * [--overrides=<overrides>]
	 * : Set up configuration overrides in the format "key1=value1,key2=value2"
	 *
	 * ## EXAMPLES
	 *
	 *     wp akeeba backup take --profile=2
	 *
	 *     wp akeeba backup take --description="Before changing menu on [DATE] [TIME]"
	 *
	 * @when  after_wp_load
	 * @alias run
	 * @alias backup
	 *
	 * @param   array $args       Positional arguments (literal arguments)
	 * @param   array $assoc_args Associative arguments (--flag, --no-flag, --key=value)
	 *
	 * @return  void
	 *
	 * @throws  WP_CLI\ExitException
	 */
	public function takeBackup($args, $assoc_args)
	{
		/** @var  Application $akeebaBackupApplication */
		global $akeebaBackupApplication;

		WP_CLI::debug("About to start a backup with Akeeba Backup.", 'akeebabackup');
		$mark = microtime(true);

		// Get the backup options
		$profile     = isset($assoc_args['profile']) ? (int) $assoc_args['profile'] : 1;
		$description = isset($assoc_args['description']) ? trim($assoc_args['description']) : '';
		$comment     = isset($assoc_args['comment']) ? trim($assoc_args['comment']) : '';
		$backupid    = isset($assoc_args['backupid']) ? trim($assoc_args['backupid']) : '';
		$container = $akeebaBackupApplication->getContainer();

		if (empty($description))
		{
			$model       = Model::getTmpInstance($container->application_name, 'Backup', $container);
			$description = $model->getDefaultDescription() . ' (WP-CLI)';
		}

		$overrides = isset($assoc_args['overrides']) ? $this->getOverrides($assoc_args['overrides']) : [];

		// Make sure $profile is a positive integer >= 1
		$profile = max(1, $profile);

		// Otherwise the Engine doesn't set a backup ID
		$backupid = empty($backupid) ? null : $backupid;

		// Set the active profile
		WP_CLI::debug("Setting active profile to $profile", 'akeebabackup');
		$session   = $container->segment;
		$session->set('profile', $profile);

		/**
		 * DO NOT REMOVE!
		 *
		 * The Model will only try to load the configuration after nuking the factory. This causes Profile 1 to be
		 * loaded first. Then it figures out it needs to load a different profile and it does – but the protected keys
		 * are NOT replaced, meaning that certain configuration parameters are not replaced. Most notably, the chain.
		 * This causes backups to behave weirdly. So, DON'T REMOVE THIS UNLESS WE REFACTOR THE MODEL.
		 */
		Platform::getInstance()->load_configuration($profile);

		WP_CLI::debug("Initializing the backup model", 'akeebabackup');
		/** @var \Solo\Model\Backup $model */
		$model = Model::getTmpInstance($container->application_name, 'Backup', $container);

		// Dummy array so that the loop iterates once
		$array = [
			'HasRun'       => 0,
			'Error'        => '',
			'cli_firstrun' => 1,
		];

		$model->setState('tag', AKEEBA_BACKUP_ORIGIN);
		$model->setState('backupid', $backupid);
		$model->setState('description', $description);
		$model->setState('comment', $comment);

		while (($array['HasRun'] != 1) && (empty($array['Error'])))
		{
			$myOverrides = [];

			if (isset($array['cli_firstrun']) && $array['cli_firstrun'])
			{
				WP_CLI::log("Starting backup using profile #$profile.");

				$array = $model->startBackup(array_merge([
					'akeeba.tuning.min_exec_time'           => 0,
					'akeeba.tuning.max_exec_time'           => 15,
					'akeeba.tuning.run_time_bias'           => 100,
					'akeeba.advanced.autoresume'            => 0,
					'akeeba.tuning.nobreak.beforelargefile' => 1,
					'akeeba.tuning.nobreak.afterlargefile'  => 1,
					'akeeba.tuning.nobreak.proactive'       => 1,
					'akeeba.tuning.nobreak.finalization'    => 1,
					'akeeba.tuning.settimelimit'            => 0,
					'akeeba.tuning.setmemlimit'             => 0,
					'akeeba.tuning.nobreak.domains'         => 0,
				], $overrides));
			}
			else
			{
				WP_CLI::log("Continuing backup.");
				$array = $model->stepBackup();
			}

			$stepText = empty($array['Step']) ? '' : ", step ‘{$array['Step']}’";
			WP_CLI::log("Completed a backup step in domain ‘{$array['Domain']}’$stepText.");

			$time     = date('Y-m-d H:i:s \G\M\TO (T)');
			$memusage = $this->memUsage();

			WP_CLI::debug("Last Tick   : $time");
			WP_CLI::debug("Domain      : {$array['Domain']}");
			WP_CLI::debug("Step        : {$array['Step']}");
			WP_CLI::debug("Substep     : {$array['Substep']}");
			WP_CLI::debug("Memory used : $memusage");

			if (!empty($array['Warnings']))
			{
				foreach ($array['Warnings'] as $line)
				{
					WP_CLI::warning($line);
				}
			}

			// Recycle the database connection to minimise problems with database timeouts
			$db = Factory::getDatabase();
			$db->close();
			$db->open();

			// Reset the backup timer
			Factory::getTimer()->resetTime();
		}

		$peakMemory = $this->peakMemUsage();
		$elapsed    = $this->timeago($mark, time(), '', false);

		WP_CLI::debug("Peak memory used : $peakMemory");
		WP_CLI::debug("Backup loop exited after $elapsed");

		if (!empty($array['Error']))
		{
			WP_CLI::error($array['Error']);
		}

		WP_CLI::success("The backup process is now complete.");
	}

	/**
	 * Parse the overrides provided in the command line.
	 *
	 * Input: "key1=value1, key2= value2, key3 = value3"
	 * Output: ['key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3']
	 *
	 * @param   string $rawString The raw string
	 *
	 * @return  array  The parsed overrides
	 */
	private function getOverrides($rawString)
	{
		if (empty($rawString) || (trim($rawString) == ''))
		{
			return [];
		}

		$rawString = trim($rawString);
		$ret       = [];
		$lines     = explode($rawString, ",");

		foreach ($lines as $line)
		{
			if (strpos($line, '=') === false)
			{
				continue;
			}

			list($key, $value) = explode('=', $line);
			$key       = trim($key);
			$value     = trim($value);
			$ret[$key] = $value;
		}

		return $ret;
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
	 * Returns a fancy formatted time lapse code
	 *
	 * @param   integer        $referenceDateTime Timestamp of the reference date/time
	 * @param   string|integer $currentDateTime   Timestamp of the current date/time
	 * @param   string         $measureBy         One of s, m, h, d, or y (time unit)
	 * @param   boolean        $autoText          Append text automatically?
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
}
