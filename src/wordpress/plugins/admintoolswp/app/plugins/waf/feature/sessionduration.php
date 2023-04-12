<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureSessionduration extends AtsystemFeatureAbstract
{
	protected $loadOrder = 70;

	public function isEnabled()
	{
		$login    = ($this->cparams->getValue('sessionnumduration', '') && $this->cparams->getValue('sessionduration', ''));
		$remember = ($this->cparams->getValue('sessionnumduration_remember', '') &&
						$this->cparams->getValue('sessionduration_remember', ''));

		// If the user changed the session duration for the regular or the remember me login, this feature is enabled
		return ($login || $remember);
	}

	/**
	 * Changes the default expiration time to something customized by the user
	 *
	 * @param   int     $expiration     Default expiration defined by WordPress
	 * @param   int     $user_id        User id
	 * @param   bool    $remember       Was the option "Remember me" checked?
	 *
	 * @return int
	 */
	public function onSessionStart($expiration, $user_id, $remember)
	{
		// Am I handling the "Remember me" scenario?
		if ($remember)
		{
			$duration = $this->cparams->getValue('sessionduration_remember', '');
			$num      = $this->cparams->getValue('sessionnumduration_remember', '');

			if ($duration && $num)
			{
				$duration   = $this->decodeDuration($duration);
				$expiration = $duration * $num;
			}
		}
		else
		{
			$duration = $this->cparams->getValue('sessionduration', '');
			$num      = $this->cparams->getValue('sessionnumduration', '');

			if ($duration && $num)
			{
				$duration   = $this->decodeDuration($duration);
				$expiration = $duration * $num;
			}
		}

		return $expiration;
	}

	private function decodeDuration($duration)
	{
		switch ($duration)
		{
			case 'hour':
				$result = 60 * 60;
				break;
			case 'day':
				$result = 60 * 60 * 24;
				break;
			case 'month':
				$result = 60 * 60 * 24 * 30;
				break;
			default:
				throw new Exception(sprintf("Can't decode duration %s", $duration));
		}

		return $result;
	}
}
