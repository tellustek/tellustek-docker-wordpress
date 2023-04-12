<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo\View\Discover;


use Akeeba\Engine\Factory;
use Awf\Mvc\View;
use Awf\Text\Text;
use Awf\Utils\Template;
use Solo\Model\Discover;

class Html extends View
{
	/**
	 * The directory we are currently listing
	 *
	 * @var  string
	 */
	public $directory;

	/**
	 * The list of importable archive files in the current directory
	 *
	 * @var  array
	 */
	public $files;

	/**
	 * Push state variables before showing the main page
	 *
	 * @return  boolean
	 */
	public function onBeforeMain()
	{
		// Load the necessary Javascript
		Template::addJs('media://js/solo/showon.js', $this->container->application);
		Template::addJs('media://js/solo/configuration.js', $this->container->application);
		Template::addJs('media://js/solo/discover.js', $this->container->application);

		/** @var Discover $model */
		$model = $this->getModel();

		$directory       = $model->getState('directory', '', 'path');
		$this->directory = $directory;

		if (empty($directory))
		{
			$config          = Factory::getConfiguration();
			$this->directory = $config->get('akeeba.basic.output_directory', '[DEFAULT_OUTPUT]');
		}

		// Push translations
		Text::script('COM_AKEEBA_CONFIG_UI_BROWSE');
		Text::script('COM_AKEEBA_FILEFILTERS_LABEL_UIROOT');

		$document = $this->container->application->getDocument();
		$router   = $this->container->router;

		$document->addScriptOptions('akeeba.Discover.URLs.browser', $router->route('index.php?view=browser&tmpl=component&processfolder=1&folder='));

		return true;
	}

	/**
	 * Push state variables before showing the discovery page
	 *
	 * @return  boolean
	 */
	public function onBeforeDiscover()
	{
		/** @var Discover $model */
		$model = $this->getModel();

		$directory = $model->getState('directory', '');
		$this->setLayout('discover');

		$files = $model->getFiles();

		$this->files     = $files;
		$this->directory = $directory;

		return true;
	}
} 
