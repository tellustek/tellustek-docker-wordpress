<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo\Controller;


use Awf\Text\Text;
use Awf\Timer\Timer;
use Exception;

class Alice extends ControllerDefault
{
	/**
	 * Start scanning the log file. Calls step().
	 *
	 * @throws Exception
	 * @see  step()
	 *
	 */
	public function start()
	{
		// Make sure we have an anti-CSRF token
		$this->csrfProtection();

		// Reset the model state and tell which log file we'll be scanning
		/** @var \Solo\Model\Alice $model */
		$model = $this->getModel();
		$model->reset($this->input->get('log', '', 'cmd'));

		// Run the first step.
		$this->step();
	}

	/**
	 * @throws Exception
	 */
	public function step()
	{
		// Make sure we have an anti-CSRF token
		$this->csrfProtection();

		// Run a scanner step
		/** @var \Solo\Model\Alice $model */
		$model = $this->getModel();
		$timer = new Timer(4, 75);

		try
		{
			$finished = $model->analyze($timer);
		}
		catch (Exception $e)
		{
			// Error in the scanner: show the error page
			$this->container->segment->set('aliceException', $e);
			$this->setRedirect($this->container->router->route('index.php?view=Alice&task=error'));

			return;
		}

		if ($finished)
		{
			$this->getView()->setLayout('result');
			$this->doTask = 'result';
			$this->display();

			return;
		}

		$this->getView()->setLayout('step');
		$this->display();
	}
}
