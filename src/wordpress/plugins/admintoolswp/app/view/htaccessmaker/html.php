<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\HtaccessMaker;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\ServerTechnology;
use Akeeba\AdminTools\Admin\Model\HtaccessMaker;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	/** @var    string  .htaccess contents for preview */
	public $htaccess;

	/** @var    \stdClass   The .htaccess Maker configuration */
	public $htconfig;

	/** @var    int     Is this supported? 0 No, 1 Yes, 2 Maybe */
	public $isSupported;

	protected function onBeforePreview()
	{
		/** @var HtaccessMaker $model */
		$model          = $this->getModel();
		$this->htaccess = $model->makeConfigFile();
		$this->setLayout('plain');
	}

	protected function onBeforeDisplay()
	{
		/** @var HtaccessMaker $model */
		$model             = $this->getModel();
		$this->htconfig    = $model->loadConfiguration();
		$this->isSupported = ServerTechnology::isHtaccessSupported();
	}
}
