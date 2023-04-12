<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\AdvancedWPConfig;

use Akeeba\AdminTools\Admin\Helper\Params;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Admin\Model\AdvancedWPConfig;
use Akeeba\AdminTools\Admin\Model\ConfigureWAF;

defined('ADMINTOOLSINC') or die;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	/** @var \stdClass WordPress Configuration */
	public $wpconfig;

	/** @var array	WAF Configuration  */
	public $wafconfig = [];

	/** @var bool Should we display the options as tabbed or a long list? */
	public $longConfig = false;

	protected function onBeforeDisplay()
	{
		/** @var AdvancedWPConfig $model */
		$model          = $this->getModel();
		$this->wpconfig = (object)$model->loadConfiguration();

		// We're going to fetch the WAF Configuration, too. Since some features will be triggered by our plugin
		// (ie custom error reporting) it's way easier to store those values there so we can fetch the value back
		/** @var ConfigureWAF $waf_config */
		$waf_config = $this->getModel('ConfigureWAF');
		$this->wafconfig = $waf_config->getItems();

		Wordpress::enqueueScript('tooltip.js');
		Wordpress::enqueueScript('advancedwpconfig.js');

		$params = Params::getInstance();
		$this->longConfig = $params->getValue('longConfig', 0);

		if (!$this->longConfig)
		{
			Wordpress::enqueueScript('../fef/js/tabs.min.js');
		}
	}
}
