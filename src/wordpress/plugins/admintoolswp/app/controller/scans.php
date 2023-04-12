<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Library\Input\Input;
use Akeeba\AdminTools\Library\Mvc\Controller\Controller;

defined('ADMINTOOLSINC') or die;

class Scans extends Controller
{
	public function __construct(Input $input)
	{
		parent::__construct($input);

		if (!defined('AKEEBADEBUG'))
		{
			if (defined('WP_DEBUG'))
			{
				define('AKEEBADEBUG', WP_DEBUG);
			}
		}
	}

	/**
	 * Apply hard-coded filters before rendering the Browse page
	 */
	protected function onBeforeDisplay()
	{
		/** @var \Akeeba\AdminTools\Admin\Model\Scans $model */
		$model = $this->getModel();

		$modelInput = $model->getInput();
		$modelInput->set('complete', 1);

		// Force the model to always display only the completed scans
		$model->setInput($modelInput);
	}

	public function display()
	{
		// Currently we don't have onAfter* events, so let's do the old way
		parent::display();

		/** @var \Akeeba\AdminTools\Admin\Model\Scans $model */
		$model = $this->getModel();

		$model->removeIncompleteScans();
	}

	/**
	 * I have to override parent save, so I can change default redirect
	 */
	public function save()
	{
		parent::save();

		$id = $this->input->getInt('id', 0);

		$this->redirect(ADMINTOOLSWP_URL.'&view=ScanAlerts&scan_id='.$id);
	}

	public function add()
	{
		throw new \Exception(Language::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
	}

	public function startscan()
	{
		// Cleanup current output so far
		@ob_get_clean();

		/** @var \Akeeba\AdminTools\Admin\View\Scans\Html $view */
		$view = $this->getView();
		/** @var \Akeeba\AdminTools\Admin\Model\Scans $model */
		$model = $this->getModel();

		$view->retarray = $model->startScan();
		$view->setLayout('scan');

		$this->display();
	}

	public function stepscan()
	{
		// Cleanup current output so far
		@ob_get_clean();

		/** @var \Akeeba\AdminTools\Admin\View\Scans\Html $view */
		$view = $this->getView();
		/** @var \Akeeba\AdminTools\Admin\Model\Scans $model */
		$model = $this->getModel();

		$view->retarray = $model->stepScan();
		$view->setLayout('scan');

		$this->display();
	}

	public function purge()
	{
		/** @var \Akeeba\AdminTools\Admin\Model\Scans $model */
		$model = $this->getModel();

		$type = null;

		if($model->purgeFilesCache())
		{
			$msg = Language::_('COM_ADMINTOOLS_MSG_SCAN_PURGE_COMPLETED');
		}
		else
		{
			$msg = Language::_('COM_ADMINTOOLS_MSG_SCAN_PURGE_ERROR');
			$type = 'error';
		}

		$this->getView()->enqueueMessage($msg, $type);

		$this->redirect(ADMINTOOLSWP_URL.'&view=Scans');
	}
}
