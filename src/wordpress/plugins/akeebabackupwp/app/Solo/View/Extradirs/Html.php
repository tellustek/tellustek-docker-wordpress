<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo\View\Extradirs;

use Awf\Mvc\View;
use Awf\Text\Text;
use Awf\Utils\Template;
use Solo\View\ViewTraits\ProfileIdAndName;

class Html extends View
{
	use ProfileIdAndName;

	/**
	 * Execute before displaying the main and only page of the off-site files inclusion page
	 *
	 * @return  boolean
	 */
	public function onBeforeMain()
	{
		Template::addJs('media://js/solo/showon.js', $this->container->application);
		Template::addJs('media://js/solo/configuration.js', $this->container->application);
		Template::addJs('media://js/solo/fsfilters.js', $this->container->application);
		Template::addJs('media://js/solo/extradirs.js', $this->container->application);

		// Get a JSON representation of the directories data
		/** @var \Solo\Model\Extradirs $model */
		$model    = $this->getModel();
		$document = $this->container->application->getDocument();
		$router   = $this->container->router;

		$document->addScriptOptions('akeeba.System.params.AjaxURL', $router->route('index.php?view=Extradirs&task=ajax'));
		$document->addScriptOptions('akeeba.Configuration.URLs', [
			'browser' => $router->route('index.php?view=Browser&processfolder=1&tmpl=component&folder='),
		]);
		$document->addScriptOptions('akeeba.IncludeFolders.guiData', $model->get_directories());

		$this->getProfileIdAndName();

		// Push translations
		Text::script('COM_AKEEBA_FILEFILTERS_LABEL_UIROOT');
		Text::script('COM_AKEEBA_FILEFILTERS_LABEL_UIERRORFILTER');

		return true;
	}
} 
