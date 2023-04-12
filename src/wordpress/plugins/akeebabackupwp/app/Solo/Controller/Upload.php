<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo\Controller;

use Akeeba\Engine\Platform;
use Awf\Text\Text;
use Exception;
use Solo\Model\Mixin\GetErrorsFromExceptions;
use Solo\View\Upload\Html;

class Upload extends ControllerDefault
{
	use GetErrorsFromExceptions;

	/**
	 * This controller does not have a default task
	 *
	 * @return  void
	 *
	 * @throws \RuntimeException
	 */
	public function main()
	{
		throw new \RuntimeException('Invalid task', 500);
	}

	/**
	 * Start uploading
	 *
	 * @return  void
	 */
	public function start()
	{
		$id = $this->getAndCheckId();

		$router = $this->container->router;
		$returnUrl = $router->route('index.php?view=upload&tmpl=component&task=cancelled&id=' . $id);

		// Check the backup stat ID
		if ($id === false)
		{
			$this->setRedirect($returnUrl, Text::_('COM_AKEEBA_TRANSFER_ERR_INVALIDID'), 'error');

			return;
		}

		// Start by resetting the saved post-processing engine
		$session = $this->container->segment;
		$session->set('upload_factory', null);

		// Initialise the view
		/** @var Html $view */
		$view = $this->getView();

		$view->done  = 0;
		$view->error = 0;

		$view->id = $id;
		$view->setLayout('default');

		$this->display();
	}

	/**
	 * This task steps the upload and displays the results
	 *
	 * @return  void
	 */
	public function upload()
	{
		// Get the parameters
		$id = $this->getAndCheckId();

		$router = $this->container->router;
		$returnUrl = $router->route('index.php?view=upload&tmpl=component&task=cancelled&id=' . $id);

		$part = $this->input->get('part', 0, 'int');
		$frag = $this->input->get('frag', 0, 'int');

		// Check the backup stat ID
		if ($id === false)
		{
			$this->setRedirect($returnUrl, Text::_('COM_AKEEBA_TRANSFER_ERR_INVALIDID'), 'error');

			return;
		}

		/**
		 * Get the View and initialize its layout
		 * @var Html $view
		 */
		$view        = $this->getView();
		$view->done  = 0;
		$view->error = 0;

		$view->setLayout('uploading');

		try
		{
			/** @var \Solo\Model\Upload $model */
			$model  = $this->getModel();
			$result = $model->upload($id, $part, $frag);
		}
		catch (Exception $e)
		{
			// If we have an error we have to display it and stop the upload
			$view->done         = 0;
			$view->error        = 1;
			$view->errorMessage = implode("\n", $this->getErrorsFromExceptions($e));

			$view->setLayout('error');

			// Also reset the saved post-processing engine
			$this->container->segment->set('upload_factory', null);

			$this->display();

			return;
		}
		finally
		{
			// Get the modified model state
			$part = $model->getState('part');
			$stat = $model->getState('stat');

			// Push the state to the view. We assume we have to continue uploading. We only change that if we detect an
			// upload completion or error condition in the if-blocks further below.
			$view->parts = $stat['multipart'];
			$view->part  = $part;
			$view->frag  = $model->getState('frag');
			$view->id    = $model->getState('id');
		}

		if (($part >= 0) && ($result === true))
		{
			// If we are told the upload finished successfully we can display the "done" page
			$view->setLayout('done');
			$view->done  = 1;
			$view->error = 0;

			// Also reset the saved post-processing engine
			$this->container->segment->set('upload_factory', null);
		}

		$this->display();
	}

	/**
	 * This task shows the error page when the upload fails for any reason
	 *
	 * @return  void
	 */
	public function cancelled()
	{
		/** @var Html $view */
		$view = $this->getView();
		$view->setLayout('error');

		$this->display();
	}

	/**
	 * Gets the stats record ID from the request and checks that it does exist
	 *
	 * @return  boolean|integer  False if an invalid ID is found, the numeric ID if it's valid
	 */
	private function getAndCheckId()
	{
		$id = $this->input->get('id', 0, 'int');

		if ($id <= 0)
		{
			return false;
		}

		$statObject = Platform::getInstance()->get_statistics($id);

		if (empty($statObject) || !is_array($statObject))
		{
			return false;
		}

		return $id;
	}
} 
