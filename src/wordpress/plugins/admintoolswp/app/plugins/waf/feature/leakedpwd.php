<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureLeakedpwd extends AtsystemFeatureAbstract
{
	protected $loadOrder = 900;

	public function isEnabled()
	{
		// Protect vs broken host
		if (!function_exists('sha1'))
		{
			return false;
		}

		return ($this->cparams->getValue('leakedpwd', 0) == 1);
	}

	/**
	 * Hooks into the Joomla! models before a user is saved.
	 *
	 * @param   \WP_Error   $errors     Holds the full stack of WP errors
	 * @param   bool        $update     Is this a new user or updating an existing one?
	 * @param   \stdClass   $user       WordPress user class
	 *
	 * @throws  Exception  When we catch a security exception
	 */
	public function onUserBeforeSave(&$errors, $update, &$user)
	{
		if (!isset($user->user_pass) || !$user->user_pass)
		{
			return;
		}

		// Check that this is a role we want to protect
		$roles = $this->cparams->getValue('leakedpwd_roles', '');
		$roles = explode(',', $roles);

		if (!$roles)
		{
			return;
		}

		// If the user was already saved I can query WP to get all the data
		// Otherwise if it's a new one I can get it from the $user variable
		if (isset($user->ID))
		{
			$userData 	  = get_userdata($user->ID);
			$user_roles   = $userData->roles;
		}
		else
		{
			$user_roles = array($user->role);
		}

		$should_check = false;

		foreach ($user_roles as $user_role)
		{
			if (in_array($user_role, $roles))
			{
				$should_check = true;
				break;
			}
		}

		// No roles found? This means that we shouldn't check for leaked passwords
		if (!$should_check)
		{
			return;
		}

		// HIBP database searches for the first 5 chars, if the rest of the hash is in the response body, the password
		// is included in a leaked database
		$hashed = strtoupper(sha1($user->user_pass));
		$search = substr($hashed, 0, 5);
		$body	= substr($hashed, 5);

		$response = wp_remote_get('https://api.pwnedpasswords.com/range/'.$search, array('user-agent', 'admin-tools-pwd-checker'));

		// Something bad happened, do not die on that
		if ($response instanceof WP_Error)
		{
			return;
		}

		// This should never happen, but better be safe than sorry
		if ($response['response']['code'] !== 200)
		{
			return;
		}

		// There's no need to further process the response: if the rest of the hash is inside the body,
		// it means that is an insecure password
		if (strpos($response['body'], $body) !== false)
		{
			$errors->add(403, Language::sprintf('COM_ADMINTOOLS_LEAKEDPWD_ERR', $user->user_pass));
		}
	}
}
