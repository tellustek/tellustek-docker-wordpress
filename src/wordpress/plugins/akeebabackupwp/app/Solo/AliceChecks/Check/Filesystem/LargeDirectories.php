<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo\Alice\Check\Filesystem;

use Awf\Container\Container;
use Solo\Alice\Check\Base;
use Awf\Text\Text;

/**
 * Checks if the user is trying to backup directories with a lot of files
 */
class LargeDirectories extends Base
{
	public function __construct(Container $container, $logFile = null)
	{
		$this->priority         = 30;
		$this->checkLanguageKey = 'COM_AKEEBA_ALICE_ANALYZE_FILESYSTEM_LARGE_DIRECTORIES';

		parent::__construct($container, $logFile);
	}

	public function check()
	{
		$prev_dir  = '';
		$large_dir = [];

		$this->scanLines(function ($data) use (&$prev_dir, &$large_dir) {
			// Let's get all the involved directories
			preg_match_all('#Scanning files of <root>/(.*)#', $data, $matches);

			if (!isset($matches[1]) || empty($matches[1]))
			{
				return;
			}

			$dirs = $matches[1];

			if ($prev_dir)
			{
				array_unshift($dirs, $prev_dir);
			}

			foreach ($dirs as $dir)
			{
				preg_match_all('#Adding ' . $dir . '/([^\/]*) to#', $data, $tmp_matches);

				if (count($tmp_matches[0]) > 250)
				{
					$large_dir[] = ['position' => $dir, 'elements' => count($tmp_matches[0])];
				}
			}

			$prev_dir = array_pop($dirs);
		});

		if (empty($large_dir))
		{
			return;
		}

		$errorMsg = [];

		// Let's log all the results
		foreach ($large_dir as $dir)
		{
			$errorMsg[] = $dir['position'] . ', ' . $dir['elements'] . ' files';
		}

		$this->setResult(-1);
		$this->setErrorLanguageKey([
			'COM_AKEEBA_ALICE_ANALYZE_FILESYSTEM_LARGE_DIRECTORIES_ERROR', implode("\n", $errorMsg),
		]);
	}

	public function getSolution()
	{
		return Text::_('COM_AKEEBA_ALICE_ANALYZE_FILESYSTEM_LARGE_DIRECTORIES_SOLUTION');
	}
}
