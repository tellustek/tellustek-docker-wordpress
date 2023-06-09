<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo\Model\Json\Task;

use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use Solo\Model\Json\TaskInterface;

/**
 * Get information for a given backup record
 */
class GetBackupInfo implements TaskInterface
{
	/**
	 * Return the JSON API task's name ("method" name). Remote clients will use it to call us.
	 *
	 * @return  string
	 */
	public function getMethodName()
	{
		return 'getBackupInfo';
	}

	/**
	 * Execute the JSON API task
	 *
	 * @param   array $parameters The parameters to this task
	 *
	 * @return  mixed
	 *
	 * @throws  \RuntimeException  In case of an error
	 */
	public function execute(array $parameters = array())
	{
		// Get the passed configuration values
		$defConfig = array(
			'backup_id' => 0,
		);

		$defConfig = array_merge($defConfig, $parameters);

		$backup_id = (int)$defConfig['backup_id'];

		// Get the basic statistics
		$record = Platform::getInstance()->get_statistics($backup_id);

		// Backup record doesn't exist
		if (empty($record))
		{
			throw new \RuntimeException('Invalid backup record identifier', 404);
		}

		// Get a list of filenames
		$filenames = Factory::getStatistics()->get_all_filenames($record);

		if (empty($filenames))
		{
			// Archives are not stored on the server or no files produced
			$record['filenames'] = array();
		}
		else
		{
			$filedata = array();
			$i        = 0;

			// Get file sizes per part
			foreach ($filenames as $file)
			{
				$i++;
				$size       = @filesize($file);
				$size       = is_numeric($size) ? $size : 0;
				$filedata[] = array(
					'part' => $i,
					'name' => basename($file),
					'size' => $size
				);
			}

			// Add the file info to $record['filenames']
			$record['filenames'] = $filedata;
		}

		return $record;
	}
}
