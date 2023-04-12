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

class AutoBannedAddresses extends Model
{
	public function __construct(Input $input)
	{
		parent::__construct($input);

		$this->pk    = 'ip';
		$this->table = '#__admintools_ipautoban';
	}

	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('#__admintools_ipautoban'));

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

	/**
	 * Deletes one or more records
	 *
	 * @param null|array	$ids	List of ID that should be deleted
	 *
	 * @return bool
	 */
	public function delete($ids = null)
	{
		if (!$this->pk || !$this->table)
		{
			return false;
		}

		$db = $this->db;

		$ids = (array) $ids;

		if (!$ids)
		{
			$ids = $this->input->get('cid', [], 'raw');
		}

		$query = $db->getQuery(true)
			->delete($db->qn($this->table))
			->where($db->qn($this->pk) . ' IN(' . implode(',', array_map([$db, 'q'], $ids)) . ')');
		$db->setQuery($query)->execute();

		// Call the "onAfterDelete" on each record
		if (method_exists($this, 'onAfterDelete'))
		{
			foreach ($ids as $id)
			{
				$this->onAfterDelete($id);
			}
		}

		return true;
	}

}
