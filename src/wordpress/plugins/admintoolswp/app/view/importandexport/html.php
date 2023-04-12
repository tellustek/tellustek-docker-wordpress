<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\ImportAndExport;

use Akeeba\AdminTools\Admin\Helper\Wordpress;

defined('ADMINTOOLSINC') or die;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	protected function onBeforeExport()
	{
		Wordpress::enqueueScript('importexport.js');
	}

}
