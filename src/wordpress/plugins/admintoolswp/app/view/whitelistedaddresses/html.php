<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\WhitelistedAddresses;

use Akeeba\AdminTools\Admin\Helper\Storage;
use Akeeba\AdminTools\Admin\Model\ControlPanel;
use Akeeba\AdminTools\Admin\Model\WhitelistedAddresses;

defined('ADMINTOOLSINC') or die;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	protected $myIP;
	protected $whitelist_enabled;

	protected function onBeforeDisplay()
	{
		/** @var WhitelistedAddresses $model */
		$model = $this->getModel();

		$this->items = $model->getItems();
		$this->total = $model->getTotal();
		$this->limitstart = $this->input->getInt('limitstart', 0);

		$cparams = Storage::getInstance();
		$this->whitelist_enabled = ($cparams->getValue('ipwl', 0) == 1);
	}

	protected function onBeforeAdd()
	{
		$this->populateMyIP();
	}

	protected function onBeforeEdit()
	{
		$this->populateMyIP();

		/** @var WhitelistedAddresses $model */
		$model = $this->getModel();
		$id    = $this->input->getInt('id', 0);

		$this->item = $model->getItem($id);
	}

	private function populateMyIP()
	{
		/** @var ControlPanel $cpanelModel */
		$cpanelModel = $this->getModel('ControlPanel');
		$this->myIP  = $cpanelModel->getVisitorIP();
	}
}
