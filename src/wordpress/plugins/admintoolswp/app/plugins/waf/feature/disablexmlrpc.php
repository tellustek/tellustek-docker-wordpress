<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureDisablexmlrpc extends AtsystemFeatureAbstract
{
	protected $loadOrder = 1;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return ($this->cparams->getValue('disablexmlrpc', 0) == 1);
	}

	/**
	 * Completely blocks WordPress' XML-RPC server.
	 *
	 * The xmlrpc.php file will still be accessible but it will consistently return error 405 “XML-RPC
	 * services are disabled on this site.”
	 */
	public function onCustomHooks()
	{
		// Seriously, that's how you tell WP to disable XML-RPC.
		add_filter( 'xmlrpc_enabled', function() {
			return false;
		} );
	}
}
