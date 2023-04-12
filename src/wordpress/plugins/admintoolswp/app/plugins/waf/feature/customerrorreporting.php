<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureCustomerrorreporting extends AtsystemFeatureAbstract
{
	protected $loadOrder = 1;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		// If user wants to apply the system default one, simply disable this feature
		return ($this->cparams->getValue('error_reporting', '') != '');
	}

	/**
	 * Change default error_reporting value to the specified one. We can't use our "onSystem" event since we have to fire
	 * as soon as possible, but AFTER WordPress was initialized; otherwise it will change back the error reporting to
	 * the default one.
	 */
	public function onWordPressLoad()
	{
		$level = $this->cparams->getValue('error_reporting', '');

		// This should never happen, but better be safe
		if (!$level)
		{
			return;
		}

		switch ($level)
		{
			case 'none':
			default:
				$code = 0;
				break;
			case 'errors':
				$code = E_ERROR;
				break;
			case 'minimal':
				$code = E_ERROR | E_WARNING | E_PARSE;
				break;
			case 'full':
				$code = E_ERROR | E_WARNING | E_PARSE | E_NOTICE;
				break;
			case 'developer':
				$code = E_ERROR | E_WARNING | E_PARSE | E_NOTICE | E_DEPRECATED;
				break;
		}

		@error_reporting($code);
	}
}
