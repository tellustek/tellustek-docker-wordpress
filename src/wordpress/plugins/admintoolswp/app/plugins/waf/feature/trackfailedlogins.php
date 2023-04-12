<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureTrackfailedlogins extends AtsystemFeatureAbstract
{
	protected $loadOrder = 800;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return ($this->cparams->getValue('trackfailedlogins', 0) == 1);
	}

	/**
	 * Treat failed logins as security exceptions
	 */
	public function onUserLoginFailure()
	{
		$extraInfo = null;
		$user      = $this->input->getString('log', null);

		// Log the username only if we have a user AND we told Admin Tools to store usernames, too
		if ($this->cparams->getValue('logusernames', 0) && !empty($user))
		{
			$extraInfo = 'Username: ' . $user;
		}

		$this->exceptionsHandler->logAndAutoban('loginfailure', $user, $extraInfo);
	}
}
