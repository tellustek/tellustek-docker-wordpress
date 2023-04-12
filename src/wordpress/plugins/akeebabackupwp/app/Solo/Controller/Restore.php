<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo\Controller;

use Awf\Text\Text;
use Exception;

class Restore extends ControllerDefault
{
	/**
	 * Show the main page, where the user selects the restoration options
	 *
	 * @return  void
	 */
	public function main()
	{
		/** @var   \Solo\Model\Restore $model */
		$model = $this->getModel();

		// Get the ID
		$id = $model->getState('id', 0);

		$cid = $this->input->get('cid', [], 'array');

		if (empty($id))
		{
			if (is_array($cid) && !empty($cid))
			{
				$id = $cid[0];
			}
			else
			{
				$id = -1;
			}
		}

		$model->setState('id', $id);

		$profileID = $this->input->getInt('profileid', 0);
		$model->setState('profileid', $profileID);

		try
		{
			$model->validateRequest();
		}
		catch (Exception $e)
		{
			$message = $e->getMessage();
			$router  = $this->container->router;

			$this->setRedirect($router->route('index.php?view=manage'), $message, 'error');
			$this->redirect();

			return;
		}

		$model->setState('restorationstep', 0);

		$this->display();
	}

	/**
	 * Show the restoration user interface and start the restoration
	 *
	 * @return  void
	 * @throws  Exception
	 */
	public function start()
	{
		$this->csrfProtection();

		$this->getView()->setLayout('restore');

		/** @var   \Solo\Model\Restore $model */
		$model = $this->getModel();

		$model->setState('restorationstep', 1);

		// This is required. validateRequest loads the correct backup profile. We need it to get the site directory.
		try
		{
			$model->validateRequest();
		}
		catch (Exception $e)
		{
			$message = $e->getMessage();
			$router  = $this->container->router;

			$this->setRedirect($router->route('index.php?view=manage'), $message, 'error');
			$this->redirect();

			return;
		}

		// Set the model's state
		$model->setState('jps_key'      , $this->input->get('jps_key', '', 'cmd'));
		$model->setState('procengine'   , $this->input->get('procengine', 'direct', 'cmd'));
		$model->setState('zapbefore'    , $this->input->get('zapbefore', 0, 'int'));
		$model->setState('stealthmode'  , $this->input->get('stealthmode', 0, 'int'));
		$model->setState('min_exec'     , $this->input->get('min_exec', 0, 'int'));
		$model->setState('max_exec'     , $this->input->get('max_exec', 5, 'int'));
		$model->setState('ftp_host'     , $this->input->get('ftp_host', '', 'none'));
		$model->setState('ftp_port'     , $this->input->get('ftp_port', 21, 'int'));
		$model->setState('ftp_user'     , $this->input->get('ftp_user', '', 'none'));
		$model->setState('ftp_pass'     , $this->input->get('ftp_pass', '', 'none'));
		$model->setState('ftp_root'     , $this->input->get('ftp_root', '', 'none'));
		$model->setState('tmp_path'     , $this->input->get('tmp_path', '', 'none'));
		$model->setState('ftp_ssl'      , $this->input->get('usessl', 'false', 'cmd') == 'true');
		$model->setState('ftp_pasv'     , $this->input->get('passive', 'true', 'cmd') == 'true');

		try
		{
			$model->createRestorationFile();
		}
		catch (Exception $e)
		{
			$router = $this->container->router;
			$this->setRedirect($router->route('index.php?view=manage'),
				Text::_('COM_AKEEBA_RESTORE_ERROR_CANT_WRITE') . '<br/>' . $e->getMessage(), 'error');
			$this->redirect();

			return;
		}

		$this->display();
	}

	/**
	 * Perform an AJAX request, returning the result encoded in JSON and surrounded by triple hashes
	 *
	 * @return  void
	 */
	public function ajax()
	{
		/** @var   \Solo\Model\Restore $model */
		$model = $this->getModel();

		$ajax = $this->input->get('ajax', '', 'cmd');
		$model->setState('ajax', $ajax);
		$ret = $model->doAjax();

		@ob_end_clean();
		echo '#"\#\"#' . json_encode($ret) . '#"\#\"#';
		flush();

		$this->container->application->close();
	}
} 
