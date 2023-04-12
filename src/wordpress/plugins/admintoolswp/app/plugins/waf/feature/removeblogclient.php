<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureRemoveblogclient extends AtsystemFeatureAbstract
{
	protected $loadOrder = 71;

	public function isEnabled()
	{
		return $this->cparams->getValue('removeblogclient', 1);
	}

	/**
	 * On our custom hook, let's ask WordPress to remove blog client links
	 */
	public function onCustomHooks()
	{
		remove_action ('wp_head', 'rsd_link');
		remove_action( 'wp_head', 'wlwmanifest_link');
	}
}
