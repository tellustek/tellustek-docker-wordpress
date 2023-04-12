<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo\Controller;

use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use Awf\Application\Application;
use Awf\Date\Date;
use Awf\Mvc\Model;
use Awf\Text\Text;
use Awf\Uri\Uri;
use Solo\Model\Backup;

class Remote extends ControllerDefault
{
	public function execute($task)
	{
		$this->checkPermissions();
		define('AKEEBA_BACKUP_ORIGIN', 'frontend');

		return parent::execute($task);
	}

	public function main()
	{
		// Set the profile
		$this->setProfile();

		// Get the backup ID
		$backupId = $this->input->get('backupid', null, 'cmd');

		if (empty($backupId))
		{
			$backupId = null;
		}

		/** @var Backup $model */
		$model = Model::getTmpInstance($this->container->application_name, 'Backup', $this->container);

		$dateNow = new Date();

		$model->setState('tag', AKEEBA_BACKUP_ORIGIN);
		$model->setState('backupid', $backupId);
		$model->setState('description', $model->getDefaultDescription() . ' (Frontend)');
		$model->setState('comment', '');

		$array = $model->startBackup();

		$backupId = $model->getState('backupid', null, 'cmd');

		$this->processEngineReturnArray($array, $backupId);
	}

	public function step()
	{
		// Set the profile
		$this->setProfile();

		// Get the backup ID
		$backupId = $this->input->get('backupid', null, 'cmd');

		if (empty($backupId))
		{
			$backupId = null;
		}

		/** @var Backup $model */
		$model = Model::getTmpInstance($this->container->application_name, 'Backup', $this->container);

		$model->setState('tag', AKEEBA_BACKUP_ORIGIN);
		$model->setState('backupid', $backupId);

		$array = $model->stepBackup();

		$backupId = $model->getState('backupid', null, 'cmd');

		$this->processEngineReturnArray($array, $backupId);
	}

	/**
	 * Used by the tasks to process Akeeba Engine's return array. Depending on the result and the component options we
	 * may throw text output or send an HTTP redirection header.
	 *
	 * @param   array   $array     The return array to process
	 * @param   string  $backupId  The backup ID (used to step the backup process)
	 */
	private function processEngineReturnArray($array, $backupId)
	{
		$noredirect = $this->input->get('noredirect', 0, 'int');

		if ($array['Error'] != '')
		{
			// An error occured
			if ($noredirect)
			{
				@ob_end_clean();
				header('Content-type: text/plain');
				header('Connection: close');
				echo '500 ERROR -- ' . $array['Error'];
				flush();
				$this->container->application->close();
			}

			throw new \RuntimeException($array['Error'], 500);
		}

		if ($array['HasRun'] == 1)
		{
			// All done
			Factory::nuke();
			Factory::getFactoryStorage()->reset();

			@ob_end_clean();
			header('Content-type: text/plain');
			header('Connection: close');
			echo '200 OK';
			flush();

			$this->container->application->close();
		}

		if ($noredirect != 0)
		{
			@ob_end_clean();
			header('Content-type: text/plain');
			header('Connection: close');
			echo '301 More work required -- BACKUPID #"\#\"#' . $backupId . '#"\#\"#';
			flush();

			$this->container->application->close();
		}

		if (defined('WPINC'))
		{
			$uri = new Uri(admin_url('admin-ajax.php?action=akeebabackup_legacy'));
			$uri->setVar('task', 'step');
			$uri->setVar('key', $this->input->get('key', '', 'none'));
			$uri->setVar('profile', $this->input->getInt('profile', 1));

			if (!empty($backupId))
			{
				$uri->setVar('backupid', $backupId);
			}

			wp_redirect($uri->toString(), 307, 'Akeeba Backup for WordPress');

			// If we're triggering the backup using the ajax endpoint, we have to explicitly set the redirection code,
			// otherwise WordPress will automatically set an HTTP status of 200
			if (wp_doing_ajax())
			{
				wp_die('0', '', ['response' => 307]);
			}
		}

		$router = $this->container->router;
		$key    = urlencode($this->input->get('key', '', 'none', 2));
		$url    = 'remote.php?view=remote&task=step&key=' . $key . '&profile=' . $this->input->get('profile', 1, 'int');

		if (!empty($backupId))
		{
			$url .= '&backupid=' . $backupId;
		}

		$this->setRedirect($router->route($url));
	}

	/**
	 * Check that the user has sufficient permissions, or die in error
	 *
	 * @return  void
	 */
	private function checkPermissions()
	{
		// Is frontend backup enabled?
		$febEnabled = Platform::getInstance()->get_platform_configuration_option('legacyapi_enabled', 0);
		$febEnabled = in_array($febEnabled, ['on', 'checked', 'true', 1, 'yes']);

		$validKey = Platform::getInstance()->get_platform_configuration_option('frontend_secret_word', '');

		if (!\Akeeba\Engine\Util\Complexify::isStrongEnough($validKey, false))
		{
			$febEnabled = false;
		}

		$validKeyTrim = trim($validKey);

		if (!$febEnabled || empty($validKey))
		{
			@ob_end_clean();
			header('Content-type: text/plain');
			header('Connection: close');
			echo "403 Operation not permitted";
			flush();

			$this->container->application->close();

			throw new \RuntimeException('Operation not permitted', 403);
		}

		// Is the key good?
		$key = $this->input->get('key', '', 'none', 2);

		if (($key != $validKey) || (empty($validKeyTrim)))
		{
			@ob_end_clean();
			header('Content-type: text/plain');
			header('Connection: close');
			echo "403 Operation not permitted";
			flush();

			$this->container->application->close();

			throw new \RuntimeException('Operation not permitted', 403);
		}
	}

	/**
	 * Set the active profile from the input parameters
	 */
	private function setProfile()
	{
		// Set profile
		$profile = $this->input->get('profile', 1, 'int');

		if (empty($profile))
		{
			$profile = 1;
		}

		$session          = Application::getInstance()->getContainer()->segment;
		$session->profile = $profile;

		/**
		 * DO NOT REMOVE!
		 *
		 * The Model will only try to load the configuration after nuking the factory. This causes Profile 1 to be
		 * loaded first. Then it figures out it needs to load a different profile and it does – but the protected keys
		 * are NOT replaced, meaning that certain configuration parameters are not replaced. Most notably, the chain.
		 * This causes backups to behave weirdly. So, DON'T REMOVE THIS UNLESS WE REFACTOR THE MODEL.
		 */
		Platform::getInstance()->load_configuration($profile);
	}
} 
