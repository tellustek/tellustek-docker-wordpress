<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Library\Mvc\Model\Model;

defined('ADMINTOOLSINC') or die;


class ScanAlerts extends Model
{
	public function __construct($input)
	{
		parent::__construct($input);

		$this->table = '#__admintools_scanalerts';
		$this->pk    = 'admintools_scanalert_id';
	}

	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
	                ->select(array(
		               $db->qn('admintools_scanalert_id'),
		               'IF(' . $db->qn('diff') . ' != "",0,1) AS ' . $db->qn('newfile'),
		               'IF(' . $db->qn('diff') . ' LIKE "###SUSPICIOUS FILE###%",1,0) AS ' . $db->qn('suspicious'),
		               'IF(' . $db->qn('diff') . ' != "",' .
		               'IF(' . $db->qn('diff') . ' LIKE "###SUSPICIOUS FILE###%",' .
		               $db->q('0-suspicious') . ',' . $db->q('2-modified') . ')'
		               . ',' . $db->q('1-new') . ') AS ' . $db->qn('filestatus'),
		               $db->qn('path'),
		               $db->qn('threat_score'),
		               $db->qn('acknowledged'),
		               $db->qn('scan_id'),
	                ))
					->from($db->qn('#__admintools_scanalerts'));

		$scan_id = $this->input->getInt('scan_id', 0);

		if ($scan_id)
		{
			$query->where($db->qn('scan_id').' = '.$db->q($scan_id));
		}

		$search = $this->input->getString('search', '');

		if ($search)
		{
			$query->where($db->qn('path') . ' LIKE ' . $db->q('%' . $search . '%'));
		}

		$status = $this->input->getString('status', '');

		switch ($status)
		{
			case 'new':
				$query->where('IF(' . $db->qn('diff') . ' != "",0,1) = ' . $db->q(1));
				break;

			case 'suspicious':
				$query->where('IF(' . $db->qn('diff') . ' LIKE "###SUSPICIOUS FILE###%",1,0)  = ' . $db->q(1));
				break;

			case 'modified':
				$query->where('IF(' . $db->qn('diff') . ' != "",0,1) = ' . $db->q(0));
				$query->where('IF(' . $db->qn('diff') . ' LIKE "###SUSPICIOUS FILE###%",1,0)  = ' . $db->q(0));
				break;
		}

		$safe = $this->input->getString('safe', '');

		if (is_numeric($safe) && ($safe != '-1'))
		{
			$query->where($db->qn('acknowledged') . ' = ' . $db->q($safe));
		}

		if (!$overrideLimits)
		{
			$order = $this->input->getCmd('ordering', null);
			$dir   = $this->input->getCmd('order_dir', 'ASC');

			if (!in_array($order, array('path', 'threat_score', 'acknowledged', 'filestatus', 'newfile', 'suspcious')))
			{
				$order = 'threat_score';
				$dir   = 'DESC';
			}

			$query->order($db->qn($order) . ' ' . $dir);
		}

		return $query;
	}

	public function save(array $data = array())
	{
		$db = $this->getDbo();
		$id = $this->input->getInt('admintools_scanalert_id', 0);

		if (!$id)
		{
			throw new \RuntimeException(Language::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));
		}

		$data = (object) array(
			'admintools_scanalert_id' => $id,
			'acknowledged' => $this->input->getInt('acknowledged', 0)
		);

		$db->updateObject('#__admintools_scanalerts', $data, array('admintools_scanalert_id'));

		return $data->admintools_scanalert_id;
	}

	public function setPublished($ids, $status)
	{
		$status  = (int) $status;
		$cleaned = array();

		foreach ($ids as $id)
		{
			$cleaned[] = (int) $id;
		}

		$db = $this->getDbo();

		$query = $db->getQuery(true)
					->update($db->qn($this->table))
					->set($db->qn('acknowledged').' = '.$db->q($status))
					->where($db->qn($this->pk).' IN('.implode(',', $cleaned).')');

		$db->setQuery($query)->execute();
	}

	/**
	 * Mark all entries of the specified scan as safe.
	 *
	 * @param   int  $scan_id  The ID of the scan
	 *
	 * @since   1.0.0.rc2
	 */
	public function markAllSafe($scan_id)
	{
		$scan_id = max(0, (int) $scan_id);

		if ($scan_id == 0)
		{
			return;
		}

		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->update($db->qn($this->table))
			->set([
				$db->qn('acknowledged') . ' = ' . $db->q(1),
			])
			->where($db->qn('scan_id') . ' = ' . $db->q($scan_id))
			->where($db->qn('threat_score') . ' > ' . $db->q(0));
		$db->setQuery($query)->execute();
	}

}
