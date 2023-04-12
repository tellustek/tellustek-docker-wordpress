<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo\View\Multidb;

use Awf\Mvc\View;
use Awf\Text\Text;
use Awf\Uri\Uri;
use Awf\Utils\Template;
use Solo\Helper\Escape;
use Solo\Model\Multidb;
use Solo\View\ViewTraits\ProfileIdAndName;

class Html extends View
{
	use ProfileIdAndName;

	public function onBeforeMain()
	{
		Template::addJs('media://js/solo/fsfilters.js', $this->container->application);
		Template::addJs('media://js/solo/multidb.js', $this->container->application);

		// Get a JSON representation of the database connection data
		/** @var Multidb $model */
		$model = $this->getModel();

		$this->getProfileIdAndName();

		// Push translations
		Text::script('COM_AKEEBA_FILEFILTERS_UIROOT');
		Text::script('COM_AKEEBA_FILEFILTERS_LABEL_UIERRORFILTER');
		Text::script('COM_AKEEBA_MULTIDB_GUI_LBL_HOST');
		Text::script('COM_AKEEBA_MULTIDB_GUI_LBL_PORT');
		Text::script('COM_AKEEBA_MULTIDB_GUI_LBL_USERNAME');
		Text::script('COM_AKEEBA_MULTIDB_GUI_LBL_PASSWORD');
		Text::script('COM_AKEEBA_MULTIDB_GUI_LBL_DATABASE');
		Text::script('COM_AKEEBA_MULTIDB_GUI_LBL_PREFIX');
		Text::script('COM_AKEEBA_MULTIDB_GUI_LBL_TEST');
		Text::script('COM_AKEEBA_MULTIDB_GUI_LBL_SAVE');
		Text::script('COM_AKEEBA_MULTIDB_GUI_LBL_CANCEL');
		Text::script('COM_AKEEBA_MULTIDB_GUI_LBL_LOADING');
		Text::script('COM_AKEEBA_MULTIDB_GUI_LBL_CONNECTOK');
		Text::script('COM_AKEEBA_MULTIDB_GUI_LBL_CONNECTFAIL');
		Text::script('COM_AKEEBA_MULTIDB_GUI_LBL_SAVEFAIL');
		Text::script('COM_AKEEBA_MULTIDB_GUI_LBL_DRIVER');

		$document = $this->container->application->getDocument();
		$router   = $this->container->router;

		$document->addScriptOptions('akeeba.System.params.AjaxURL', $router->route('index.php?view=Multidb&task=ajax'));
		$document->addScriptOptions('akeeba.Multidb.loadingGif', Template::parsePath('media://image/loading.gif'));
		$document->addScriptOptions('akeeba.Multidb.guiData', $model->get_databases());


		return true;
	}
} 
