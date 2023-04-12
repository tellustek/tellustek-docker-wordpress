<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\SchedulingInformation;

use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Admin\Model\SchedulingInformation;

defined('ADMINTOOLSINC') or die;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	/** @var    object  Info about scheduling */
	public $croninfo;

	protected function onBeforeDisplay()
	{
		/** @var SchedulingInformation $model */
		$model = $this->getModel();

		// Get the CRON paths
		$this->croninfo  = $model->getPaths();

		Wordpress::enqueueScript('../fef/js/tabs.min.js');
		Wordpress::enqueueScript('schedulinginfo.js');
	}
}
