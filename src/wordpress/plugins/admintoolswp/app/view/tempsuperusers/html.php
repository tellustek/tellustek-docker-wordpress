<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\TempSuperUsers;

use Akeeba\AdminTools\Admin\Model\TempSuperUsers;

defined('ADMINTOOLSINC') or die;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	public $userInfo = [];

	protected function onBeforeDisplay()
	{
		/** @var TempSuperUsers $model */
		$model = $this->getModel();

		$this->items = $model->getItems();
		$this->total = $model->getTotal();
		$this->limitstart = $this->input->getInt('limitstart', 0);
	}

	protected function onBeforeAdd()
	{
		$this->setLayout('wizard');

		/** @var TempSuperUsers $model */
		$model          = $this->getModel();
		$this->userInfo = $model->getNewUserData();
	}

	protected function onBeforeEdit()
	{
		/** @var TempSuperUsers $model */
		$model = $this->getModel();
		$id    = $this->input->getInt('id', 0);

		$this->item = $model->getItem($id);
	}
}
