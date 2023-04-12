<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Library\Utils\Ip;

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureIpwhitelist extends AtsystemFeatureAbstract
{
	protected $loadOrder = 50;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return ($this->cparams->getValue('ipwl', 0) == 1);
	}

	/**
	 * Filters login access by IP. If the IP of the visitor is not included
	 * in the whitelist, he gets a login error
	 *
	 * @param   WP_Error|WP_User    $user
	 * @param   string              $username
	 * @param   string              $password
	 *
	 * @return  WP_User|WP_Error
	 */
	public function onUserAuthenticate($user, $username, $password)
	{
		// If everything is empty, the user is doing the logout. Do not run since there's no need
		if (!$user && !$username && !$password)
		{
			return null;
		}

		// Another hook raised an error, let's stop here. This should never happen, since we *SHOULD* be the first
		// one to fire
		if ($user instanceof WP_Error)
		{
			return $user;
		}

		// WordPress simply passes an empty user, we have to fetch the id back from the db
		$db = $this->db;

		$query = $db->getQuery(true)
					->select($db->qn('ID'))
					->from($db->qn('#__users'))
					->where($db->qn('user_login').' = '.$db->q($username));

		try
		{
			$userid = $db->setQuery($query)->loadResult();
		}
		catch (Exception $e)
		{
			// Do not break the site if anything bad happens
			return $user;
		}

		// Non-existing user, nothing to do
		if (!$userid)
		{
			return $user;
		}

		// Only run if the user is actually an administrator. We do not want to block regular users
		$userid = (int) $userid;

		if (Wordpress::getUserAdminLevel($userid) < 2)
		{
			return $user;
		}

		// Let's get a list of allowed IP ranges
		$query = $db->getQuery(true)
					->select($db->qn('ip'))
					->from($db->qn('#__admintools_adminiplist'));
		$db->setQuery($query);

		try
		{
			$ipTable = $db->loadColumn();
		}
		catch (Exception $e)
		{
			// Do nothing if the query fails
			$ipTable = null;
		}

		if (empty($ipTable))
		{
			return $user;
		}

		$ip     = Ip::getIp();
		$inList = Ip::IPinList($ip, $ipTable);

		if ($inList === false)
		{
			$this->exceptionsHandler->logAndAutoban('ipwl');

			// Let's redirect the user to the home page
			$this->redirectToHome();
		}

		return $user;
	}
}
