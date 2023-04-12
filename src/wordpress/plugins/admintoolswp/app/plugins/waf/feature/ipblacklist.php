<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Library\Utils\Ip;

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureIpblacklist extends AtsystemFeatureAbstract
{
	protected $loadOrder = 20;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return ($this->cparams->getValue('ipbl', 0) == 1);
	}

	/**
	 * Filters visitor access by IP. If the IP of the visitor is included in the
	 * blacklist, she gets a 403 error
	 */
	public function onSystem()
	{
		// Let's get a list of blocked IP ranges
		$db = $this->db;
		$sql = $db->getQuery(true)
			->select($db->qn('ip'))
			->from($db->qn('#__admintools_ipblock'));
		$db->setQuery($sql);

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
			return;
		}

		$ip     = Ip::getIp();
		$inList = Ip::IPinList($ip, $ipTable);

		if ($inList !== true)
		{
			return;
		}

		$message = $this->cparams->getValue('custom403msg', '');

		if (empty($message))
		{
			$message = 'ADMINTOOLS_BLOCKED_MESSAGE';
		}

		$message = Language::_($message);

		if ($message == 'ADMINTOOLS_BLOCKED_MESSAGE')
		{
			$message = "Access Denied";
		}

		@ob_end_clean();
		header("HTTP/1.0 403 Forbidden");

		echo $message;
		die();
	}
}
