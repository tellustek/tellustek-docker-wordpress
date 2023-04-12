<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo\View\Restore;

use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use Awf\Mvc\View;
use Awf\Text\Text;
use Awf\Uri\Uri;
use Awf\Utils\Template;

class Html extends View
{
	public $password;
	public $id;
	public $ftpparams;
	public $extractionmodes;
	public $extension;
	public $siteURL;

	public function display($tpl = null)
	{
		$this->loadCommonJavascript();

		return parent::display($tpl);
	}

	public function onBeforeMain()
	{
		/** @var \Solo\Model\Restore $model */
		$model = $this->getModel();

		$this->id              = $model->getState('id', 0, 'int');
		$this->ftpparams       = $model->getFTPParams();
		$this->extractionmodes = $model->getExtractionModes();

		$backup          = Platform::getInstance()->get_statistics($this->id);
		$this->extension = strtolower(substr($backup['absolute_path'], -3));

		$router   = $this->container->router;
		$document = $this->container->application->getDocument();

		$document->addScriptOptions('akeeba.Configuration.URLs', [
			'browser'    => $router->route('index.php?view=browser&tmpl=component&processfolder=1&folder='),
			'ftpBrowser' => $router->route('index.php?view=ftpbrowser'),
			'testFtp'    => $router->route('index.php?view=restore&task=ajax&ajax=testftp'),
		]);

		return true;
	}

	public function onBeforeStart()
	{
		/** @var \Solo\Model\Restore $model */
		$model = $this->getModel();

		$inCMS = $this->container->segment->get('insideCMS', false);

		if ($inCMS)
		{
			$this->siteURL = $this->container->appConfig->get('cms_url', '');
		}
		else
		{
			$this->siteURL = Factory::getConfiguration()->get('akeeba.platform.site_url', '');
		}

		$this->siteURL = trim($this->siteURL);
		$this->siteURL = rtrim($this->siteURL, '/');

		$this->setLayout('restore');

		$document = $this->container->application->getDocument();

		$document->addScriptOptions('akeeba.Restore.password', $model->getState('password'));
		$document->addScriptOptions('akeeba.Restore.ajaxURL', Uri::base(false, $this->container) . 'restore.php');
		$document->addScriptOptions('akeeba.Restore.mainURL', $this->siteURL);
		$document->addScriptOptions('akeeba.Restore.inMainRestoration', true);

		return true;
	}

	public function loadCommonJavascript()
	{
		Template::addJs('media://js/solo/showon.js', $this->container->application);
		Template::addJs('media://js/solo/configuration.js', $this->container->application);
		Template::addJs('media://js/solo/restore.js', $this->container->application);

		// Push translations
		Text::script('COM_AKEEBA_CONFIG_UI_BROWSE');
		Text::script('COM_AKEEBA_CONFIG_UI_CONFIG');
		Text::script('COM_AKEEBA_CONFIG_UI_REFRESH');
		Text::script('COM_AKEEBA_FILEFILTERS_LABEL_UIROOT');
		Text::script('COM_AKEEBA_CONFIG_UI_FTPBROWSER_TITLE');
		Text::script('COM_AKEEBA_CONFIG_DIRECTFTP_TEST_OK');
		Text::script('COM_AKEEBA_CONFIG_DIRECTFTP_TEST_FAIL');
		Text::script('COM_AKEEBA_CONFIG_DIRECTSFTP_TEST_OK');
		Text::script('COM_AKEEBA_CONFIG_DIRECTSFTP_TEST_FAIL');
		Text::script('COM_AKEEBA_BACKUP_TEXT_LASTRESPONSE');
	}

} 
