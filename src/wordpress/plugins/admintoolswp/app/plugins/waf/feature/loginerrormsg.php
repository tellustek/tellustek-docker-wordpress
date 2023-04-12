<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureLoginerrormsg extends AtsystemFeatureAbstract
{
	protected $loadOrder = 800;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return ($this->cparams->getValue('loginerrormsg', '') != '');
	}

	/**
	 * Overrides standard WordPress login error message with something more obscure
	 *
	 * @param   string  $error  Original error message
	 *
	 * @return  string
	 */
	public function onLoginErrorMessage($error)
	{
		return $this->cparams->getValue('loginerrormsg', $error);
	}
}
