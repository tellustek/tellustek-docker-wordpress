<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

use Akeeba\AdminTools\Library\Input\Input;
use Akeeba\AdminTools\Library\Mvc\Model\Model;

defined('ADMINTOOLSINC') or die;

class IPAutoBanHistories extends Model
{
	public function __construct(Input $input)
	{
		parent::__construct($input);

		$this->pk    = 'id';
		$this->table = '#__admintools_ipautobanhistory';
	}

	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('#__admintools_ipautobanhistory'));

		$fltIP = $this->input->getString('ip', null);

		if ($fltIP)
		{
			$fltIP = '%' . $fltIP . '%';
			$query->where($db->qn('ip') . ' LIKE ' . $db->q($fltIP));
		}

		if (!$overrideLimits)
		{
			$ordering  = $this->input->getCmd('ordering', '');
			$direction = $this->input->getCmd('order_dir', '');

			if (!in_array($ordering, array('reason', 'until', 'ip')))
			{
				$ordering = 'until';
			}

			if (!in_array($direction, array('asc', 'desc')))
			{
				$direction = 'desc';
			}

			$query->order($db->qn($ordering).' '.$direction);
		}

		return $query;
	}
}
