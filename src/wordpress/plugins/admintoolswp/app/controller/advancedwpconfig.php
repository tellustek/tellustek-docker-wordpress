<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Library\Mvc\Controller\Controller;

defined('ADMINTOOLSINC') or die;

class AdvancedWPConfig extends Controller
{
	public function preview()
	{
		parent::display();
	}

	public function save()
	{
		// CSRF prevention
		$this->csrfProtection();

		$data = $this->input->getData();

		/** @var \Akeeba\AdminTools\Admin\Model\AdvancedWPConfig $model */
		$model = $this->getModel();
		$model->saveConfiguration($data);

		/** @var \Akeeba\AdminTools\Admin\Model\ConfigureWAF $waf_model */
		$waf_model = $this->getModel('ConfigureWAF');
		$waf_model->saveConfig($data);

		$this->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_SAVED'));

		$this->redirect(ADMINTOOLSWP_URL.'&view=AdvancedWPConfig');
	}

	public function apply()
	{
		// CSRF prevention
		$this->csrfProtection();

		$data = $this->input->getData();

		/** @var \Akeeba\AdminTools\Admin\Model\AdvancedWPConfig $model */
		$model = $this->getModel();
		$model->saveConfiguration($data);

		/** @var \Akeeba\AdminTools\Admin\Model\ConfigureWAF $waf_model */
		$waf_model = $this->getModel('ConfigureWAF');
		$waf_model->saveConfig($data);

		$status = $model->writeConfigFile();

		if (!$status)
		{
			$this->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_NOTAPPLIED'), 'error');
			$this->redirect(ADMINTOOLSWP_URL.'&view=AdvancedWPConfig');
		}

		$this->getView()->enqueueMessage(Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_APPLIED'));
		$this->redirect(ADMINTOOLSWP_URL.'&view=AdvancedWPConfig');
	}
}
