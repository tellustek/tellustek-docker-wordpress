<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\Scans;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Admin\Model\Scans;

defined('ADMINTOOLSINC') or die;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	protected function onBeforeDisplay()
	{
		Wordpress::enqueueScript('modal.js');
		Wordpress::enqueueScript('scan.js');

		/** @var Scans $model */
		$model = $this->getModel();

		$this->items = $model->getItems();
		$this->total = $model->getTotal();
		$this->limitstart = $this->input->getInt('limitstart', 0);
	}

	protected function onBeforeEdit()
	{
		/** @var Scans $model */
		$model = $this->getModel();
		$id    = $this->input->getInt('id', 0);

		$this->item = $model->getItem($id);
	}
}
