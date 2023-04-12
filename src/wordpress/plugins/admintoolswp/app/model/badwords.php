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

class BadWords extends Model
{
	public function __construct(Input $input)
	{
		parent::__construct($input);

		$this->pk    = 'id';
		$this->table = '#__admintools_badwords';
	}

	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('#__admintools_badwords'));

		$fltWord = $this->input->getString('word', null);

		if ($fltWord)
		{
			$fltWord = '%' . $fltWord . '%';
			$query->where($db->qn('word') . ' LIKE ' . $db->q($fltWord));
		}

		if (!$overrideLimits)
		{
			$ordering  = $this->input->getCmd('ordering', '');
			$direction = $this->input->getCmd('order_dir', '');

			if (!in_array($ordering, array('id', 'word')))
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
				'word' => $this->input->getString('word', '')
			);
		}

		if (!isset($data['id']))
		{
			$data['id'] = '';
		}

		$data = (object) $data;

		if (!$data->word)
		{
			throw new \RuntimeException(Language::_('COM_ADMINTOOLS_ERR_BADWORD_NEEDS_WORD'));
		}

		if (!$data->id)
		{
			$db->insertObject('#__admintools_badwords', $data, 'id');
		}
		else
		{
			$db->updateObject('#__admintools_badwords', $data, array('id'));
		}

		return $data->id;
	}
}
