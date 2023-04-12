<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Wordpress;

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureNonewadmins extends AtsystemFeatureAbstract
{
	protected $loadOrder = 210;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return ($this->cparams->getValue('nonewadmins', 0) == 1);
	}

	/**
	 * Disables creating new admins
	 *
	 * @param   \WP_Error   $errors     Holds the full stack of WP errors
	 * @param   bool        $update     Is this a new user or updating an existing one?
	 * @param   \stdClass   $user       WordPress user class
	 */
	public function onUserBeforeSave(&$errors, $update, &$user)
	{
		if ($update)
		{
			$old_user = get_user_by('id', $user->ID);

			// User wasn't an administrator and now he wants to become one, let's block it
			// Please note: we have to check the roles of the new user since it's no saved in the database yet
			// so WordPress didn't create the whole capabilities array
			if (Wordpress::getUserAdminLevel($old_user) < 2 && $user->role == 'administrator')
			{
				$this->logAndAutoban($errors, $user);
			}
		}
		else
		{
			// We're trying to add a new administrator (or better)
			if ($user->role == 'administrator')
			{
				$this->logAndAutoban($errors, $user);
			}
		}
	}

	/**
	 * @param WP_Error	$errors
	 * @param $user
	 */
	private function logAndAutoban($errors, $user)
	{
		// Log and autoban security exception
		$extraInfo = "User Data Variables :\n";
		$extraInfo .= print_r($user, true);
		$extraInfo .= "\n";

		// Display the error only if the user should be really blocked (ie we're not in the Whitelist)
		if ($this->exceptionsHandler->logAndAutoban('nonewadmins', $extraInfo))
		{
			$errors->add('admintoolswp', 'You can&#8217;t give users that role.');
		}
	}
}
