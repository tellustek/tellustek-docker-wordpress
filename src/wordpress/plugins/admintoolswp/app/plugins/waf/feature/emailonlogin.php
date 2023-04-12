<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Library\Uri\Uri;
use Akeeba\AdminTools\Library\Utils\Ip;

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureEmailonlogin extends AtsystemFeatureAbstract
{
	protected $loadOrder = 220;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		$email = $this->cparams->getValue('emailonadminlogin', '');

		return !empty($email);
	}

	/**
	 * Sends an email upon accessing an administrator page other than the login screen
	 *
	 * @param   string    $user_login
	 * @param   \WP_User  $user
	 */
	public function onUserLogin($user_login, $user = null)
	{
		// No username, user ID or user object provided. Can't do much, sorry.
		if (empty($user_login) && empty($user))
		{
			return;
		}

		// The username was provided but not a user ID or user object. Get the user object.
		if (empty($user) || (!is_int($user) && !(is_object($user) && ($user instanceof WP_User))))
		{
			$user = get_user_by('login', $user_login);

			if ($user === false)
			{
				return;
			}
		}

		// No username was provided. Try to find it.
		if (empty($user_login))
		{
			if (!isset($user->user_login) || empty($user->user_login))
			{
				return;
			}

			$user_login = $user->user_login;
		}

		// Run only for admins
		if (Wordpress::getUserAdminLevel($user) < 2)
		{
			return;
		}

		// Get the username
		$username = $user_login;
		$sitename = get_bloginfo('name');

		// Get the IP address
		$ip = Ip::getIp();

		if ((strpos($ip, '::') === 0) && (strstr($ip, '.') !== false))
		{
			$ip = substr($ip, strrpos($ip, ':') + 1);
		}

		$country   = '(unknown country)';
		$continent = '(unknown continent)';

		$uri = Uri::getInstance();
		$url = $uri->toString(['scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment']);

		$ip_link = $this->cparams->getValue('iplookupscheme', 'http') . '://' . $this->cparams->getValue('iplookup', 'ip-lookup.net/index.php?ip={ip}');
		$ip_link = str_replace('{ip}', $ip, $ip_link);

		// Construct the replacement table
		$substitutions = [
			'[SITENAME]'  => $sitename,
			'[REASON]'    => Language::_('COM_ADMINTOOLS_WAFEMAILTEMPLATE_REASON_ADMINLOGINSUCCESS'),
			'[DATE]'      => gmdate('Y-m-d H:i:s') . " GMT",
			'[URL]'       => $url,
			'[USER]'      => $username,
			'[IP]'        => $ip,
			'[LOOKUP]'    => '<a href="' . $ip_link . '">IP Lookup</a>',
			'[COUNTRY]'   => $country,
			'[CONTINENT]' => $continent,
			'[UA]'        => $_SERVER['HTTP_USER_AGENT'],
		];

		// Let's get the most suitable email template
		$template = $this->exceptionsHandler->getEmailTemplate('adminloginsuccess');

		// Got no template, the user didn't published any email template, or the template doesn't want us to
		// send a notification email. Anyway, let's stop here.
		if (!$template)
		{
			return;
		}

		$subject = $template[0];
		$body    = $template[1];

		foreach ($substitutions as $k => $v)
		{
			$subject = str_replace($k, $v, $subject);
			$body    = str_replace($k, $v, $body);
		}

		// Send the email
		$recipients = explode(',', $this->cparams->getValue('emailonadminlogin', ''));
		$recipients = array_map('trim', $recipients);

		Wordpress::sendEmail($recipients, $subject, $body, false);
	}
}
