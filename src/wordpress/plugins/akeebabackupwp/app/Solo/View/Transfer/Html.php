<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo\View\Transfer;

use Awf\Date\Date;
use Awf\Mvc\View;
use Awf\Text\Text;
use Awf\Utils\Template;

class Html extends View
{
	/** @var   array|null  Latest backup information */
	public $latestBackup = [];

	/** @var   string  Date of the latest backup, human readable */
	public $lastBackupDate = '';

	/** @var   array  Space required on the target server */
	public $spaceRequired = [
		'size'   => 0,
		'string' => '0.00 KB',
	];

	/** @var   string  The URL to the site we are restoring to (from the session) */
	public $newSiteUrl = '';

	public $newSiteUrlResult;

	/** @var   array  Results of support and firewall status of the known file transfer methods */
	public $ftpSupport = [
		'supported'  => [
			'ftp'  => false,
			'ftps' => false,
			'sftp' => false,
		],
		'firewalled' => [
			'ftp'  => false,
			'ftps' => false,
			'sftp' => false,
		],
	];

	/** @var   array  Available transfer options, for use by JHTML */
	public $transferOptions = [];

	/** @var   array  Available chunk options, for use by JHTML */
	public $chunkOptions = [];

	/** @var   array  Available chunk size options, for use by JHTML */
	public $chunkSizeOptions = [];

	/** @var   bool  Do I have supported but firewalled methods? */
	public $hasFirewalledMethods = false;

	/** @var   string  Currently selected transfer option */
	public $transferOption = 'manual';

	/** @var   string  Currently selected chunk option */
	public $chunkMode = 'chunked';

	/** @var   string  Currently selected chunk size */
	public $chunkSize = 5242880;

	/** @var   string  FTP/SFTP host name */
	public $ftpHost = '';

	/** @var   string  FTP/SFTP port (empty for default port) */
	public $ftpPort = '';

	/** @var   string  FTP/SFTP username */
	public $ftpUsername = '';

	/** @var   string  FTP/SFTP password – or certificate password if you're using SFTP with SSL certificates */
	public $ftpPassword = '';

	/** @var   string  SFTP public key certificate path */
	public $ftpPubKey = '';

	/** @var   string  SFTP private key certificate path */
	public $ftpPrivateKey = '';

	/** @var   string  FTP/SFTP directory to the new site's root */
	public $ftpDirectory = '';

	/** @var   string  FTP passive mode (default is true) */
	public $ftpPassive = true;

	/** @var   string  FTP passive mode workaround, for FTP/FTPS over cURL (default is true) */
	public $ftpPassiveFix = true;

	/** @var   int     Forces the transfer by skipping some checks on the target site */
	public $force = 0;

	/**
	 * Runs on the wizard (default) task
	 *
	 * @param   string|null  $tpl  Ignored
	 *
	 * @return  bool  True to let the view display
	 */
	public function onBeforeWizard($tpl = null)
	{
		$button = [
			'title' => Text::_('COM_AKEEBA_TRANSFER_BTN_RESET'),
			'class' => 'akeeba-btn--orange',
			'url'   => $this->getContainer()->router->route('index.php?view=transfer&task=reset'),
			'icon'  => 'akion-refresh',
		];

		$document = $this->container->application->getDocument();
		$document->getToolbar()->addButtonFromDefinition($button);

		Template::addJs('media://js/solo/transfer.js', $this->container->application);

		/** @var \Solo\Model\Transfers $model */
		$model   = $this->getModel();
		$session = $this->container->segment;
		$router  = $this->container->router;

		$this->latestBackup     = $model->getLatestBackupInformation();
		$this->spaceRequired    = $model->getApproximateSpaceRequired();
		$this->newSiteUrl       = $session->get('transfer.url', '');
		$this->newSiteUrlResult = $session->get('transfer.url_status', '');
		$this->ftpSupport       = $session->get('transfer.ftpsupport', null);
		$this->transferOption   = $session->get('transfer.transferOption', null);
		$this->chunkMode        = $session->get('transfer.chunkMode', 'chunked');
		$this->chunkSize        = $session->get('transfer.chunkSize', 5242880);
		$this->ftpHost          = $session->get('transfer.ftpHost', null);
		$this->ftpPort          = $session->get('transfer.ftpPort', null);
		$this->ftpUsername      = $session->get('transfer.ftpUsername', null);
		$this->ftpPassword      = $session->get('transfer.ftpPassword', null);
		$this->ftpPubKey        = $session->get('transfer.ftpPubKey', null);
		$this->ftpPrivateKey    = $session->get('transfer.ftpPrivateKey', null);
		$this->ftpDirectory     = $session->get('transfer.ftpDirectory', null);
		$this->ftpPassive       = $session->get('transfer.ftpPassive', 1);
		$this->ftpPassiveFix    = $session->get('transfer.ftpPassiveFix', 1);

		// We get this option from the request
		$this->force = $this->input->getInt('force', 0);

		if (!empty($this->latestBackup))
		{
			$lastBackupDate = new Date($this->latestBackup['backupstart'], 'UTC');
			$tz             = new \DateTimeZone($this->getContainer()->appConfig->get('timezone', 'UTC'));
			$lastBackupDate->setTimezone($tz);

			$this->lastBackupDate = $lastBackupDate->format(Text::_('DATE_FORMAT_LC2'), true);

			$session->set('transfer.lastBackup', $this->latestBackup);
		}

		if (empty($this->ftpSupport))
		{
			$this->ftpSupport = $model->getFTPSupport();
			$session->set('transfer.ftpsupport', $this->ftpSupport);
		}

		$this->transferOptions  = $this->getTransferMethodOptions();
		$this->chunkOptions     = $this->getChunkOptions();
		$this->chunkSizeOptions = $this->getChunkSizeOptions();

		/*
		foreach ($this->ftpSupport['firewalled'] as $method => $isFirewalled)
		{
			if ($isFirewalled && $this->ftpSupport['supported'][$method])
			{
				$this->hasFirewalledMethods = true;

				break;
			}
		}
		*/

		Text::script('COM_AKEEBA_FILEFILTERS_LABEL_UIROOT');
		Text::script('COM_AKEEBA_CONFIG_DIRECTFTP_TEST_FAIL');
		Text::script('COM_AKEEBA_FILEFILTERS_LABEL_UIROOT');
		Text::script('COM_AKEEBA_CONFIG_DIRECTFTP_TEST_FAIL');
		Text::script('COM_AKEEBA_CONFIG_UI_BROWSE');
		Text::script('COM_AKEEBA_CONFIG_UI_CONFIG');
		Text::script('COM_AKEEBA_CONFIG_UI_REFRESH');
		Text::script('COM_AKEEBA_CONFIG_UI_FTPBROWSER_TITLE');
		Text::script('COM_AKEEBA_CONFIG_DIRECTFTP_TEST_OK');
		Text::script('COM_AKEEBA_CONFIG_DIRECTSFTP_TEST_OK');
		Text::script('COM_AKEEBA_CONFIG_DIRECTSFTP_TEST_FAIL');

		$document->addScriptOptions('akeeba.System.params.AjaxURL', $router->route(sprintf("index.php?view=Transfer&format=raw&force=%d", $this->force)));
		$document->addScriptOptions('akeeba.Transfer.lastUrl', $this->newSiteUrl);
		$document->addScriptOptions('akeeba.Transfer.lastResult', $this->newSiteUrlResult);

		return true;
	}

	/**
	 * Returns the HTML options for a transfer methods drop-down, filtering out the unsupported and firewalled methods
	 *
	 * @return   array
	 */
	private function getTransferMethodOptions()
	{
		$options = [];

		foreach ($this->ftpSupport['supported'] as $method => $supported)
		{
			if (!$supported)
			{
				continue;
			}

			$methodName = Text::_('COM_AKEEBA_TRANSFER_LBL_TRANSFERMETHOD_' . $method);

			if ($this->ftpSupport['firewalled'][$method])
			{
				$methodName = '&#128274; ' . $methodName;
			}

			$options[] = ['value' => $method, 'text' => $methodName];
		}

		$options[] = ['value' => 'manual', 'text' => Text::_('COM_AKEEBA_TRANSFER_LBL_TRANSFERMETHOD_MANUALLY')];

		return $options;
	}

	/**
	 * Returns the JHTML options for a chunk methods drop-down
	 *
	 * @return   array
	 */
	private function getChunkOptions()
	{
		$options = [];

		$options[] = ['value' => 'chunked', 'text' => Text::_('COM_AKEEBA_TRANSFER_LBL_TRANSFERMODE_CHUNKED')];
		$options[] = ['value' => 'post', 'text' => Text::_('COM_AKEEBA_TRANSFER_LBL_TRANSFERMODE_POST')];

		return $options;
	}

	/**
	 * Returns the JHTML options for a chunk size drop-down
	 *
	 * @return   array
	 */
	private function getChunkSizeOptions()
	{
		$options    = [];
		$multiplier = 1048576;

		$options[] = ['value' => 0.5 * $multiplier, 'text' => '512 KB'];
		$options[] = ['value' => 1 * $multiplier, 'text' => '1 MB'];
		$options[] = ['value' => 2 * $multiplier, 'text' => '2 MB'];
		$options[] = ['value' => 5 * $multiplier, 'text' => '5 MB'];
		$options[] = ['value' => 10 * $multiplier, 'text' => '10 MB'];
		$options[] = ['value' => 20 * $multiplier, 'text' => '20 MB'];
		$options[] = ['value' => 30 * $multiplier, 'text' => '30 MB'];
		$options[] = ['value' => 50 * $multiplier, 'text' => '50 MB'];
		$options[] = ['value' => 100 * $multiplier, 'text' => '100 MB'];

		return $options;
	}
}
