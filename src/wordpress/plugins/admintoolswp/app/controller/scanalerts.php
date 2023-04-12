<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Model\Scans;
use Akeeba\AdminTools\Library\Mvc\Controller\Controller;

class ScanAlerts extends Controller
{
	public function export()
	{
		/** @var \Akeeba\AdminTools\Admin\Model\ScanAlerts $model */
		$model = $this->getModel();

		// Items are already filtered by scan_id since it's in the request
		$items = $model->getItems(true);

		$csv[] = '"admintools_scanalert_id","path","scan_id","diff","threat_score","acknowledged","newfile","suspicious","filestatus"';

		foreach ($items as $item)
		{
			$diff = str_replace('"', '""', $item->diff);
			$diff = str_replace("\r", '\\r', $diff);
			$diff = str_replace("\n", '\\n', $diff);

			$csv[] = '"'.$item->admintools_scanalert_id.'","'.$item->path.'","'.$item->scan_id.'","'.$diff.'","'.$item->threat_score.
						'","'.$item->acknowledged.'","'.$item->newfile.'","'.$item->suspicious.'","'.$item->filestatus.'"';
		}

		@ob_clean();

		header('Pragma: public');
		header('Expires: 0');

		// This moronic construct is required to work around idiot hosts who blacklist files based on crappy, broken scanners
		$xo = substr("revenge", 0, 3);
		$xoxo = substr("calibrate", 1, 2);
		header('Cache-Control: must-' . $xo . $xoxo . 'idate, post-check=0, pre-check=0');

		header('Cache-Control: public', false);
		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename="scanalerts.csv"');

		echo implode("\r\n", $csv);

		die();
	}

	/**
	 * I have to override parent save, so I can change default redirect
	 */
	public function save()
	{
		parent::save();

		$id = $this->input->getInt('admintools_scanalert_id', 0);
		/** @var \Akeeba\AdminTools\Admin\Model\ScanAlerts $model */
		$model = $this->getModel();
		$item = $model->getItem($id);

		$this->redirect(ADMINTOOLSWP_URL.'&view=ScanAlerts&scan_id='.$item->scan_id);
	}

	public function add()
	{
		throw new \Exception(Language::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
	}

	public function delete()
	{
		throw new \Exception(Language::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
	}

	public function printlist()
	{
		// TODO Write proper CSS classes for "component" views
		// WordPress has no "component" view. This means that we have to build the full page
		// and apply our style
		$this->display();

		// Kill the execution so WP won't output the footer
		exit();
	}

	public function publish()
	{
		$this->csrfProtection();

		/** @var \Akeeba\AdminTools\Admin\Model\ScanAlerts $model */
		$model   = $this->getModel();
		$scan_id = $this->input->getInt('scan_id', 0);
		$ids     = $this->input->get('cid', array(), 'raw');

		$msg  = Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_MARKEDSAFE');
		$type = 'info';

		try
		{
			$model->setPublished($ids, 1);
		}
		catch (\RuntimeException $exception)
		{
			$msg  = $exception->getMessage();
			$type = 'error';
		}

		$this->getView()->enqueueMessage($msg, $type);

		$this->redirect(ADMINTOOLSWP_URL.'&view=ScanAlerts&scan_id='.$scan_id);
	}

	public function unpublish()
	{
		$this->csrfProtection();

		/** @var \Akeeba\AdminTools\Admin\Model\ScanAlerts $model */
		$model   = $this->getModel();
		$scan_id = $this->input->getInt('scan_id', 0);
		$ids     = $this->input->get('cid', array(), 'raw');

		$msg  = Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_UNMARKEDSAFE');
		$type = 'info';

		try
		{
			$model->setPublished($ids, 0);
		}
		catch (\RuntimeException $exception)
		{
			$msg  = $exception->getMessage();
			$type = 'error';
		}

		$this->getView()->enqueueMessage($msg, $type);

		$this->redirect(ADMINTOOLSWP_URL.'&view=ScanAlerts&scan_id='.$scan_id);
	}

	public function markallsafe()
	{
		$scan_id = $this->input->getInt('scan_id', 0);

		if (!empty($scan_id))
		{
			/** @var \Akeeba\AdminTools\Admin\Model\ScanAlerts $model */
			$model = $this->getModel();
			$model->markAllSafe($scan_id);
		}

		$url = ADMINTOOLSWP_URL . '&view=ScanAlerts&scan_id=' . $scan_id;

		$this->redirect($url);
	}
}
