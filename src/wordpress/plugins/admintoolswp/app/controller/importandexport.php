<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Storage;
use Akeeba\AdminTools\Library\Mvc\Controller\Controller;

class ImportAndExport extends Controller
{
	public function export()
	{
		$this->getView()->setLayout('export');

		parent::display();
	}

	public function import()
	{
		$this->getView()->setLayout('import');

		parent::display();
	}

	public function doexport()
	{
		$this->csrfProtection();

		/** @var \Akeeba\AdminTools\Admin\Model\ImportAndExport $model */
		$model = $this->getModel();
		$data  = $model->exportData();

		if($data)
		{
			$json = json_encode($data);

			// Clear cache
			while (@ob_end_clean())
			{
				;
			}

			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: public", false);

			// Send MIME headers
			header("Content-Description: File Transfer");
			header('Content-Type: json');
			header("Accept-Ranges: bytes");
			header('Content-Disposition: attachment; filename="admintools_settings.json"');
			header('Content-Transfer-Encoding: text');
			header('Connection: close');
			header('Content-Length: ' . strlen($json));

			echo $json;

			die();
		}
		else
		{
			$this->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_IMPORTANDEXPORT_SELECT_DATA_WARN'), 'warning');
			$this->redirect(ADMINTOOLSWP_URL.'&view=ImportAndExport&task=export');
		}
	}

	public function doimport()
	{
		$this->csrfProtection();

		$params = Storage::getInstance();
		$params->setValue('quickstart', 1, true);

		/** @var \Akeeba\AdminTools\Admin\Model\ImportAndExport $model */
		$model  = $this->getModel();

		try
		{
			$model->importData();

			$type = null;
			$msg  = Language::_('COM_ADMINTOOLS_IMPORTANDEXPORT_IMPORT_OK');
		}
		catch (\Exception $e)
		{
			$type = 'error';
			$msg  = $e->getMessage();
		}

		$this->getView()->enqueueMessage($msg, $type);

		$this->redirect(ADMINTOOLSWP_URL.'&view=ImportAndExport&task=import');
	}
}
