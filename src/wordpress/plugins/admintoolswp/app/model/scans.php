<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Model\Scanner\Crawler;
use Akeeba\AdminTools\Admin\Model\Scanner\Email;
use Akeeba\AdminTools\Admin\Model\Scanner\Logger\Logger;
use Akeeba\AdminTools\Admin\Model\Scanner\Part;
use Akeeba\AdminTools\Admin\Model\Scanner\Util\Configuration;
use Akeeba\AdminTools\Admin\Model\Scanner\Util\Session;
use Akeeba\AdminTools\Library\Date\Date;
use Akeeba\AdminTools\Library\Mvc\Model\Model;
use Akeeba\AdminTools\Library\Timer\Timer;

class Scans extends Model
{
	public function __construct($input)
	{
		parent::__construct($input);

		$this->table = '#__admintools_scans';
		$this->pk    = 'id';
	}

	public function buildQuery($overrideLimits = false)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('#__admintools_scans'));

		if ($this->input->getInt('complete', 0))
		{
			$query->where($db->qn('status').' = '.$db->q('complete'));
		}

		if (!$overrideLimits)
		{
			$ordering  = $this->input->getCmd('ordering', '');
			$direction = $this->input->getCmd('order_dir', '');

			if (!in_array($ordering, array('id', 'scanstart')))
			{
				$ordering = 'id';
			}

			if (!in_array($direction, array('asc', 'desc')))
			{
				$direction = 'desc';
			}

			$query->order($db->qn($ordering).' '.$direction);
		}

		return $query;
	}

	protected function onAfterGetItems(&$resultArray)
	{
		// Don't process an empty list
		if (empty($resultArray))
		{
			return;
		}

		$this->getExtraScanInfo($resultArray);
	}

	public function getExtraScanInfo(&$items)
	{
		// Get the scan_id's and initialise the special fields
		$scanids = array();
		$map     = array();

		foreach ($items as $index => &$row)
		{
			$scanids[]       = $row->id;
			$map[ $row->id ] = $index;

			$row->files_new        = 0;
			$row->files_modified   = 0;
			$row->files_suspicious = 0;
		}

		// Fetch the stats for the IDs at hand
		$ids = implode(',', $scanids);

		$db    = $this->getDbo();
		$query = $db->getQuery(true)
					->select(array(
						$db->qn('scan_id'),
						'(' . $db->qn('diff') . ' = ' . $db->q('') . ') AS ' . $db->qn('newfile'),
						'COUNT(*) AS ' . $db->qn('count')
					))
					->from($db->qn('#__admintools_scanalerts'))
					->where($db->qn('scan_id') . ' IN (' . $ids . ')')
					->group(array(
						$db->qn('scan_id'),
						$db->qn('newfile'),
					));

		$alertstats = $db->setQuery($query)->loadObjectList();

		$query = $db->getQuery(true)
					->select(array(
						$db->qn('scan_id'),
						'COUNT(*) AS ' . $db->qn('count')
					))
					->from($db->qn('#__admintools_scanalerts'))
					->where($db->qn('scan_id') . ' IN (' . $ids . ')')
					->where($db->qn('threat_score') . ' > ' . $db->q('0'))
					->where($db->qn('acknowledged') . ' = ' . $db->q('0'))
					->group($db->qn('scan_id'));

		$suspiciousstats = $db->setQuery($query)->loadObjectList();

		// Update the $resultArray with the loaded stats
		if (!empty($alertstats))
		{
			foreach ($alertstats as $stat)
			{
				$idx = $map[ $stat->scan_id ];

				if ($stat->newfile)
				{
					$items[ $idx ]->files_new = $stat->count;
				}
				else
				{
					$items[ $idx ]->files_modified = $stat->count;
				}
			}
		}

		if (!empty($suspiciousstats))
		{
			foreach ($suspiciousstats as $stat)
			{
				$idx                             = $map[ $stat->scan_id ];
				$items[ $idx ]->files_suspicious = $stat->count;
			}
		}
	}

	public function removeIncompleteScans()
	{
		$list1 = $this->getFailed();
		$list2 = $this->getRunning();

		$list = array_merge($list1, $list2);

		unset($list1);
		unset($list2);

		if (!empty($list))
		{
			$ids = array(- 1);

			foreach ($list as $item)
			{
				$ids[] = $item->id;
			}

			$ids = implode(',', $ids);

			$db = $this->getDbo();

			$query = $db->getQuery(true)
			            ->delete('#__admintools_scans')
			            ->where($db->qn('id') . ' IN (' . $ids . ')');

			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
			            ->delete('#__admintools_scanalerts')
			            ->where($db->qn('scan_id') . ' IN (' . $ids . ')');

			$db->setQuery($query)->execute();
		}
	}

	/**
	 * Get a list of failed scans
	 *
	 * @return array
	 */
	private function getFailed()
	{
		$db = $this->getDbo();

		$query = $this->buildQuery(true);
		$query->where($db->qn('status').' = '.$db->q('fail'));

		$rows = $db->setQuery($query)->loadObjectList('id');
		$this->onAfterGetItems($rows);

		return $rows;
	}

	/**
	 * Get a list of scans still running, but not completed
	 *
	 * @return array
	 */
	private function getRunning()
	{
		$db = $this->getDbo();

		$query = $this->buildQuery(true);
		$query->where($db->qn('status').' = '.$db->q('run'));

		$rows = $db->setQuery($query)->loadObjectList('id');
		$this->onAfterGetItems($rows);

		return $rows;
	}

	protected function onAfterDelete($id)
	{
		$this->deleteScanAlerts($id);
	}

	private function deleteScanAlerts($scan_id)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
		            ->delete('#__admintools_scanalerts')
		            ->where($db->qn('scan_id') . ' = ' . $db->q($scan_id));

		$db->setQuery($query)->execute();

		return true;
	}

	/**
	 * Clears the table with files information
	 *
	 * @return  bool
	 */
	public function purgeFilesCache()
	{
		$db = $this->getDbo();

		// The best choice should be the TRUNCATE statement, however there isn't the proper function inside the driver...
		$query = $db->getQuery(true)
		            ->delete($db->qn('#__admintools_filescache'));

		try
		{
			$result = $db->setQuery($query)->execute();
		}
		catch (\Exception $e)
		{
			$result = false;
		}

		return $result;
	}

	/**
	 * Starts a new file scan
	 *
	 * @return  array
	 */
	public function startScan($origin = 'backend')
	{
		if (function_exists('set_time_limit'))
		{
			@set_time_limit(0);
		}

		// Get the scanner engine's base objects (configuration, session storage and logger)
		$configuration = Configuration::getInstance();
		$session       = Session::getInstance();
		$logger        = new Logger($configuration);

		// Log the start of a new scan
		$logger->reset();
		$logger->info(sprintf("Admin Tools Professional %s (%s)", ADMINTOOLSWP_VERSION, ADMINTOOLSWP_DATE));
		$logger->info('PHP File Change Scanner');
		$logger->info('Starting a new scan from the “' . $origin . '” origin.');

		// Get a timer according to the engine's configuration
		$maxExec     = $configuration->get('maxExec');
		$runtimeBias = $configuration->get('runtimeBias');
		$logger->debug(sprintf("Getting a new operations timer, max. exec. time %0.2fs, runtime bias %u%%", $maxExec, $runtimeBias));
		$timer = new Timer($maxExec, $runtimeBias);

		// Reset the session. This marks a brand new scan.
		$logger->debug('Resetting the session storage');
		$session->reset();

		// Create a new scan record and save its ID in the session
		$logger->debug('Creating a new scan record');
		$currentTime = new Date();

		$newScanRecord = [
			'scanstart'  => $currentTime->toSql(),
			'status'     => 'run',
			'origin'     => $origin,
			'totalfiles' => 0,
		];

		// Fetch the ID of the new scan record
		$newScanRecord['id'] = $this->save($newScanRecord);

		$logger->debug(sprintf('Scan ID: %u', $newScanRecord['id']));
		$session->set('scanID', $newScanRecord['id']);

		// Run the scanner engine
		$statusArray = $this->tickScannerEngine($configuration, $session, $logger, $timer, true);

		return $this->postProcessStatusArray($statusArray, $logger);
	}

	/**
	 * Steps the file scan
	 *
	 * @return  array
	 */
	public function stepScan()
	{
		// Get the scanner engine's base objects (configuration, session storage and logger)
		$configuration = Configuration::getInstance();
		$session       = Session::getInstance();
		$logger        = new Logger($configuration);

		// Get a timer according to the engine's configuration
		$maxExec     = $configuration->get('maxExec');
		$runtimeBias = $configuration->get('runtimeBias');
		$logger->debug(sprintf("Getting a new operations timer, max. exec. time %0.2fs, runtime bias %u%%", $maxExec, $runtimeBias));
		$timer = new Timer($maxExec, $runtimeBias);

		// Run the scanner engine
		$statusArray = $this->tickScannerEngine($configuration, $session, $logger, $timer, true);

		return $this->postProcessStatusArray($statusArray, $logger);
	}

	/**
	 * @param   Configuration    $configuration
	 * @param   Session          $session
	 * @param   Logger           $logger
	 * @param   Timer            $timer
	 * @param   bool             $enforceMinimumExecutionTime
	 *
	 * @return  array
	 *
	 * @since   5.4.0
	 */
	private function tickScannerEngine(Configuration $configuration, Session $session, Logger $logger, Timer $timer, $enforceMinimumExecutionTime = true)
	{
		// Get the crawler and step it while we have enough time left
		$crawler   = new Crawler($configuration, $session, $logger, $timer);
		$step      = $session->get('step', 0);
		$operation = 0;
		$logger->debug(sprintf('===== Starting Step #%u =====', ++$step));

		while (true)
		{
			$logger->debug(sprintf('----- Starting operation #%u -----', ++$operation));
			$statusArray = $crawler->tick();
			$logger->debug(sprintf('----- Finished operation #%u -----', $operation));

			// Did we run into an error?
			if ($crawler->getState() == Part::STATE_ERROR)
			{
				$logger->debug('The scanner engine has experienced an error.');

				break;
			}

			// Are we done?
			if ($crawler->getState() == Part::STATE_FINISHED)
			{
				$logger->debug('The scanner engine finished scanning your site.');

				break;
			}

			// Did we run out of time?
			if ($timer->getTimeLeft() <= 0)
			{
				$logger->debug('We are running out of time.');

				break;
			}

			// Is the Break Flag set?
			if ($session->get('breakFlag', false))
			{
				$logger->debug('The Break Flag is set.');

				break;
			}
		}

		$logger->debug(sprintf('===== Finished Step #%u =====', $step));

		// Reset the break flag
		$session->set('breakFlag', false);

		// Do I need to enforce the minimum execution time?
		if (!$enforceMinimumExecutionTime)
		{
			return $statusArray;
		}

		$minExec    = $configuration->get('minExec');
		$alreadyRun = $timer->getRunningTime();
		$waitTime   = $alreadyRun - $minExec;

		// Negative wait times mean that we shouldn't wait. Also, waiting for less than 10 msec is daft.
		if ($waitTime <= 0.01)
		{
			return $statusArray;
		}

		if (!function_exists('time_nanosleep'))
		{
			usleep(1000000 * $waitTime);

			return $statusArray;
		}

		$seconds    = floor($waitTime);
		$fractional = $waitTime - $seconds;
		time_nanosleep($seconds, $fractional * 1000000000);

		return $statusArray;
	}

	private function postProcessStatusArray(array $statusArray, Logger $logger)
	{
		// Get the current scan record
		$session       = Session::getInstance();
		$configuration = Configuration::getInstance();
		$scanID        = $session->get('scanID');
		$scanModel     = new Scans($this->input);
		$scanRecord    = $this->getItem($scanID);
		$currentTime   = new Date();
		$warnings      = $logger->getAndResetWarnings();

		// Apply common updates to the backup record
		$common_data = [
			'id'         => $scanID,
			'totalfiles' => $session->get('scannedFiles'),
			'scanend'    => $currentTime->toSql(),
		];

		// More work to do
		if ($statusArray['HasRun'] && (empty($statusArray['Error'])))
		{
			$logger->debug('** More work necessary. Will resume in the next step.');

			$common_data['status'] = 'run';

			$scanModel->save($common_data);

			// Still have work to do
			return [
				'status'   => true,
				'done'     => false,
				'error'    => '',
				'warnings' => $warnings,
			];
		}

		// An error occurred
		if (!empty($statusArray['Error']))
		{
			$logger->debug('** An error occurred. The scan has died.');

			$common_data['status'] = 'fail';
			$scanModel->save($common_data);
			$session->reset();

			return [
				'status'   => false,
				'done'     => true,
				'error'    => $statusArray['Error'],
				'warnings' => $warnings,
			];
		}

		// Just finished
		// -- Send emails, if necessary
		if ($scanRecord->origin != 'backend')
		{
			$logger->debug('Finished scanning. Evaluating whether to send email with scan results.');
			$email = new Email($configuration, $session, $logger);
			$email->sendEmail();
		}

		$logger->debug('** This scan is now finished.');
		$common_data['status'] = 'complete';
		$scanModel->save($common_data);
		$session->reset();

		return [
			'status'   => true,
			'done'     => true,
			'error'    => '',
			'warnings' => $warnings,
		];
	}

	public function save(array $data = array())
	{
		$db = $this->getDbo();

		if (!$data)
		{
			$data = array(
				'id' => $this->input->getInt('id', 0),
				'comment' => $this->input->getString('comment', ''),
			);
		}

		if (!isset($data['id']))
		{
			$data['id'] = '';
		}

		$data = (object) $data;

		if (!$data->id)
		{
			$db->insertObject($this->table, $data, 'id');
		}
		else
		{
			$db->updateObject($this->table, $data, array('id'));
		}

		return $data->id;
	}
}
