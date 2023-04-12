<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Wordpress;

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureBadwords extends AtsystemFeatureAbstract
{
	protected $loadOrder = 380;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		if (Wordpress::is_admin() && !Wordpress::is_ajax())
		{
			return false;
		}

		if ($this->skipFiltering)
		{
			return false;
		}

		return ($this->cparams->getValue('antispam', 0) == 1);
	}

	/**
	 * The simplest anti-spam solution imaginable. Just blocks a request if a prohibited word is found.
	 */
	public function onSystem()
	{
		$db = $this->db;
		$query = $db->getQuery(true)
					->select($db->qn('word'))
					->from($db->qn('#__admintools_badwords'))
					->group($db->qn('word'));
		$db->setQuery($query);

		try
		{
			$badwords = $db->loadColumn();
		}
		catch (Exception $e)
		{
			// Do nothing if the query fails
			$badwords = null;
		}

		if (empty($badwords))
		{
			return;
		}

		$hashes = array('get', 'post');

		foreach ($hashes as $hash)
		{
			$input   = $this->input->$hash;
			$allVars = $input->getData();

			if (empty($allVars))
			{
				continue;
			}

			foreach ($badwords as $word)
			{
				$regex = '#\b' . $word . '\b#i';

				if ($this->match_array($regex, $allVars, true))
				{
					$extraInfo = "Hash      : $hash\n";
					$extraInfo .= "Variables :\n";
					$extraInfo .= print_r($allVars, true);
					$extraInfo .= "\n";
					$this->exceptionsHandler->blockRequest('antispam', null, $extraInfo);
				}
			}
		}
	}
}
