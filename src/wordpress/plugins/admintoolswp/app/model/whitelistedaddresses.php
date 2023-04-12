<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Library\Input\Input;
use Akeeba\AdminTools\Library\Mvc\Model\Model;

defined('ADMINTOOLSINC') or die;

class WhitelistedAddresses extends Model
{
	public function __construct(Input $input)
	{
		parent::__construct($input);

		$this->pk    = 'id';
		$this->table = '#__admintools_adminiplist';
	}

	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
					->select('*')
					->from($db->qn($this->table));

		$fltIP = $this->input->getString('ip', null);

		if ($fltIP)
		{
			$fltIP = '%' . $fltIP . '%';
			$query->where($db->qn('ip') . ' LIKE ' . $db->q($fltIP));
		}

		$fltDescr = $this->input->getString('description', null);

		if ($fltDescr)
		{
			$fltDescr = '%' . $fltDescr . '%';
			$query->where($db->qn('description') . ' LIKE ' . $db->q($fltDescr));
		}

		if (!$overrideLimits)
		{
			$ordering  = $this->input->getCmd('ordering', '');
			$direction = $this->input->getCmd('order_dir', '');

			if (!in_array($ordering, array('id', 'ip')))
			{
				$ordering = 'id';
			}

			if (!in_array($direction, array('asc', 'desc')))
			{
				$direction = 'desc';
			}

			$query->order($db->qn($ordering).' '.$direction);
		}

		return $query;
	}

	public function save(array $data = array())
	{
		$db = $this->getDbo();

		if (!$data)
		{
			$data = array(
				'id' => $this->input->getInt('id', 0),
				'ip' => $this->input->getString('ip', ''),
				'description' => $this->input->getString('description', '')
			);
		}

		if (!isset($data['id']))
		{
			$data['id'] = '';
		}

		$data = (object) $data;

		if (!$data->ip)
		{
			throw new \RuntimeException(Language::_('COM_ADMINTOOLS_ERR_WHITELISTEDADDRESS_NEEDS_IP'));
		}

		if (!$data->id)
		{
			$db->insertObject($this->table, $data, 'id');
		}
		else
		{
			$db->updateObject($this->table, $data, array('id'));
		}

		return $data->id;
	}
}
