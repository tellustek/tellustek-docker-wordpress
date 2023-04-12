<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Library\Input\Input;
use Akeeba\AdminTools\Library\Mvc\Model\Model;

class ImportAndExport extends Model
{
	public function exportData()
	{
		$return = [];

		$exportData = $this->input->get('exportdata', [], 'array');

		if (isset($exportData['wafconfig']) && $exportData['wafconfig'])
		{
			/** @var ConfigureWAF $configModel */
			$configModel = new ConfigureWAF($this->input);
			$config      = $configModel->getItems();

			$return['wafconfig'] = $config;
		}

		if (isset($exportData['wafexceptions']) && $exportData['wafexceptions'])
		{
			/** @var ExceptionsFromWAF $efw */
			$efw                     = new ExceptionsFromWAF($this->input);
			$return['wafexceptions'] = $efw->getItems(true);
		}

		if (isset($exportData['ipblacklist']) && $exportData['ipblacklist'])
		{
			/** @var BlacklistedAddresses $ipBls */
			$ipBls                 = new BlacklistedAddresses($this->input);
			$return['ipblacklist'] = $ipBls->getItems(true);
		}

		if (isset($exportData['ipwhitelist']) && $exportData['ipwhitelist'])
		{
			/** @var WhitelistedAddresses $ipWls */
			$ipWls                 = new WhitelistedAddresses($this->input);
			$return['ipwhitelist'] = $ipWls->getItems(true);
		}

		if (isset($exportData['badwords']) && $exportData['badwords'])
		{
			/** @var BadWords $badwords */
			$badwords           = new BadWords($this->input);
			$return['badwords'] = $badwords->getItems(true);
		}

		if (isset($exportData['emailtemplates']) && $exportData['emailtemplates'])
		{
			/** @var WAFEmailTemplates $waftemplates */
			$waftemplates             = new WAFEmailTemplates($this->input);
			$return['emailtemplates'] = $waftemplates->getItems(true);
		}

		return $return;
	}

	public function importData()
	{
		$db = $this->getDbo();

		$input  = new Input($_FILES);
		$file   = $input->get('importfile', null, 'file');
		$errors = [];

		// Sanity checks
		if (!$file || !$file['tmp_name'])
		{
			throw new \Exception(Language::_('COM_ADMINTOOLS_IMPORTANDEXPORT_NOFILE'));
		}

		$data = file_get_contents($file['tmp_name']);

		if ($data === false)
		{
			throw new \Exception(Language::_('COM_ADMINTOOLS_ERR_IMPORTANDEXPORT_READING_FILE'));
		}

		$data = json_decode($data, true);

		if (!$data)
		{
			throw new \Exception(Language::_('COM_ADMINTOOLS_ERR_IMPORTANDEXPORT_READING_FILE'));
		}

		// Everything seems ok, let's start importing data

		if (isset($data['wafconfig']))
		{
			/** @var ConfigureWAF $config */
			$config = new ConfigureWAF($this->input);
			$config->saveConfig($data['wafconfig']);
		}

		if (isset($data['wafexceptions']))
		{
			try
			{
				$db->truncateTable('#__admintools_wafexceptions');

				if ($data['wafexceptions'])
				{
					$insert = $db->getQuery(true)
						->insert($db->qn('#__admintools_wafexceptions'))
						->columns([
							$db->qn('option'), $db->qn('view'), $db->qn('query'),
						]);

					// I could have several records, let's create a single big query
					foreach ($data['wafexceptions'] as $row)
					{
						$insert->values(
							$db->q($row['option']) . ', ' . $db->q($row['view']) . ', ' . $db->q($row['query'])
						);
					}

					$db->setQuery($insert)->execute();
				}
			}
			catch (\Exception $e)
			{
				$errors[] = Language::_('COM_ADMINTOOLS_ERR_IMPORTANDEXPORT_WAFEXCEPTIONS');
			}
		}

		if (isset($data['ipblacklist']))
		{
			try
			{
				$db->truncateTable('#__admintools_ipblock');

				if ($data['ipblacklist'])
				{
					// I could have several records, let's use raw SQL queries
					foreach ($data['ipblacklist'] as $row)
					{
						$this->importBlackListRows($row);
					}

					$this->importBlackListRows();
				}
			}
			catch (\Exception $e)
			{
				$errors[] = Language::_('COM_ADMINTOOLS_ERR_IMPORTANDEXPORT_BLACKLIST');
			}
		}

		if (isset($data['ipwhitelist']))
		{
			try
			{
				$db->truncateTable('#__admintools_adminiplist');

				if ($data['ipwhitelist'])
				{
					// I could have several records, let's create a single big query
					$insert = $db->getQuery(true)
						->insert($db->qn('#__admintools_adminiplist'))
						->columns([$db->qn('ip'), $db->qn('description')]);

					foreach ($data['ipwhitelist'] as $row)
					{
						$insert->values($db->q($row['ip']) . ', ' . $db->q($row['description']));
					}

					$db->setQuery($insert)->execute();
				}
			}
			catch (\Exception $e)
			{
				$errors[] = Language::_('COM_ADMINTOOLS_ERR_IMPORTANDEXPORT_WHITELIST');
			}
		}

		if (isset($data['badwords']))
		{
			try
			{
				$db->truncateTable('#__admintools_badwords');

				if ($data['badwords'])
				{
					// I could have several records, let's create a single big query
					$insert = $db->getQuery(true)
						->insert($db->qn('#__admintools_badwords'))
						->columns([$db->qn('word')]);

					foreach ($data['badwords'] as $row)
					{
						$insert->values($db->q($row['word']));
					}

					$db->setQuery($insert)->execute();
				}
			}
			catch (\Exception $e)
			{
				$errors[] = Language::_('COM_ADMINTOOLS_ERR_IMPORTANDEXPORT_BADWORDS');
			}
		}

		if (isset($data['emailtemplates']))
		{
			try
			{
				$db->truncateTable('#__admintools_waftemplates');
			}
			catch (\Exception $e)
			{
				$errors[] = Language::_('COM_ADMINTOOLS_ERR_IMPORTANDEXPORT_EMAILTEMPLATES');
			}

			/** @var WAFEmailTemplates $wafTemplate */
			$wafTemplate = new WAFEmailTemplates($this->input);

			// Most likely I will only have 10-12 templates max, so I can use the table instead of directly writing inside the db
			foreach ($data['emailtemplates'] as $row)
			{
				// Let's leave primary key handling to the database
				unset($row['admintools_waftemplate_id']);
				unset($row['created_by']);
				unset($row['created_on']);
				unset($row['modified_by']);
				unset($row['modified_on']);

				// Calling the save method will trigger all the checks
				try
				{
					$wafTemplate->save($row);
				}
				catch (\Exception $e)
				{
					// There was an error, better stop here
					$errors[] = Language::_('COM_ADMINTOOLS_ERR_IMPORTANDEXPORT_EMAILTEMPLATES');
					break;
				}
			}
		}

		if ($errors)
		{
			throw new \Exception(implode('<br/>', $errors));
		}
	}

	/**
	 * Since we could have several thousands of records to import, we will perform a batch import
	 *
	 * @param   null|array  $data
	 */
	protected function importBlackListRows($data = null)
	{
		static $cache = [];

		// Let's enqueue the data
		if ($data)
		{
			$cache[] = $data;
		}

		// Did we grow over the limit or are forced to flush it? If so let's build the actual query
		// and execute it
		if (count($cache) >= 100 || !$data)
		{
			$db = $this->getDbo();

			$query = $db->getQuery(true)
				->insert($db->qn('#__admintools_ipblock'))
				->columns([$db->qn('ip'), $db->qn('description')]);

			foreach ($cache as $row)
			{
				$query->values($db->q($row['ip']) . ', ' . $db->q($row['description']));
			}

			$db->setQuery($query)->execute();

			$cache = [];
		}
	}
}
