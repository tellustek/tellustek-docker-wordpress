<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo\View\Regexdbfilters;


use Awf\Html\Select;
use Awf\Mvc\View;
use Awf\Text\Text;
use Awf\Utils\Template;
use Solo\View\ViewTraits\ProfileIdAndName;

class Html extends View
{
	use ProfileIdAndName;

	/**
	 * SELECT element for choosing a database root
	 *
	 * @var  string
	 */
	public $root_select = '';

	/**
	 * List of database roots
	 *
	 * @var  array
	 */
	public $roots = [];

	/**
	 * Execute before displaying the main and only page of the off-site files inclusion page
	 *
	 * @return  boolean
	 */
	public function onBeforeMain()
	{
		Template::addJs('media://js/solo/fsfilters.js', $this->container->application);
		Template::addJs('media://js/solo/regexdbfilters.js', $this->container->application);

		/** @var \Solo\Model\Regexdbfilters $model */
		$model = $this->getModel();

		$root_info = $model->get_roots();
		$roots     = [];
		$options   = [];

		if (!empty($root_info))
		{
			// Loop all dir definitions
			foreach ($root_info as $def)
			{
				$roots[]   = $def->value;
				$options[] = Select::option($def->value, $def->text);
			}
		}
		$site_root         = '[SITEDB]';
		$this->root_select = Select::genericList($options, 'root', [
			'list.select' => $site_root,
			'id'          => 'active_root',
		]);
		$this->roots       = $roots;

		// Pass script options
		$document = $this->container->application->getDocument();
		$router   = $this->container->router;

		$document->addScriptOptions('akeeba.System.params.AjaxURL', $router->route('index.php?view=regexdbfilters&task=ajax&format=raw'));
		$document->addScriptOptions('akeeba.RegExDatabaseFilters.guiData', $model->get_regex_filters($site_root));

		$this->getProfileIdAndName();

		// Push translations
		Text::script('COM_AKEEBA_FILEFILTERS_UIROOT');
		Text::script('COM_AKEEBA_FILEFILTERS_LABEL_UIERRORFILTER');
		Text::script('COM_AKEEBA_FILEFILTERS_UIROOT');
		Text::script('COM_AKEEBA_FILEFILTERS_LABEL_UIERRORFILTER');
		Text::script('COM_AKEEBA_DBFILTER_TYPE_REGEXTABLES');
		Text::script('COM_AKEEBA_DBFILTER_TYPE_REGEXTABLEDATA');

		return true;
	}

} 
