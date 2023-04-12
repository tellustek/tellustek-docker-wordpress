<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Library\Date\Date;
use Akeeba\AdminTools\Library\Encrypt\Randval;
use Akeeba\AdminTools\Library\Input\Input;
use Akeeba\AdminTools\Library\Mvc\Model\Model;
use DateInterval;
use RuntimeException;

defined('ADMINTOOLSINC') or die;

class TempSuperUsers extends Model
{
	public function __construct(Input $input)
	{
		parent::__construct($input);

		$this->pk    = 'id';
		$this->table = '#__admintools_tempsuperusers';
	}

	/**
	 * Returns the new Super User data
	 */
	public function getNewUserData()
	{
		$rand = new Randval();

		$jDate             = new Date();
		$interval          = new DateInterval('P15D');
		$ret['expiration'] = $jDate->add($interval)->format('Y-m-d');


		$ret['username']  = 'temp' . $rand->generateString(12);
		$ret['password']  = $rand->generateString(32);
		$ret['password2'] = $ret['password'];
		$ret['email']     = $rand->generateString(12) . '@example.com';
		$ret['name']      = Language::_('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_DEFAULTNAME');
		$ret['role']      = 'administrator';

		return $ret;
	}

	/**
	 * Find an eligible super user or create a new one, then return the user ID. This is used by the Controller to
	 * create a new record.
	 *
	 * @return  int
	 */
	public function getUserIdFromInfo($info)
	{
		// Do I have an eligible existing user?
		$userId = $this->findExistingUser($info['username']);

		if (empty($userId))
		{
			// Create a new user
			$new_user_id = wp_insert_user([
				'user_pass'    => $info['password'],
				'user_login'   => $info['username'],
				'user_email'   => $info['email'],
				'display_name' => $info['name'],
				'first_name'   => $info['name'],
				'role'         => ''
			]);

			if ($new_user_id instanceof \WP_Error)
			{
				throw new RuntimeException($new_user_id->get_error_message());
			}

			return $new_user_id;
		}

		// Make sure I am not trying to edit myself
		if ($userId == get_current_user_id())
		{
			throw new RuntimeException(Language::_('COM_ADMINTOOLS_ERR_TEMPSUPERUSERS_CANTEDITSELF'), 403);
		}

		// Apply changes to the existing user
		$user = wp_get_current_user();

		$user->user_email   = $info['email'];
		$user->user_pass    = $info['password'];
		$user->display_name = $info['name'];
		$user->first_name   = $info['name'];

		$result = wp_update_user($user);

		if ($result instanceof \WP_Error)
		{
			throw new RuntimeException($result->get_error_message());
		}

		return $userId;
	}

	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
					->select('t.*, wpu.user_login, wpu.user_email')
					->from($db->qn('#__admintools_tempsuperusers', 't'))
					->innerJoin('#__users as wpu ON t.user_id = wpu.ID');

		$fltUsername = $this->input->getString('username', null);

		if ($fltUsername)
		{
			// TODO Add username filtering
		}

		if (!$overrideLimits)
		{
			$ordering  = $this->input->getCmd('ordering', '');
			$direction = $this->input->getCmd('order_dir', '');

			if (!in_array($ordering, array('id')))
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

	public function getItem($key)
	{
		$item = parent::getItem($key);

		if (!$item || !$item->user_id)
		{
			return $item;
		}

		$item->wp = get_user_by('id', $item->user_id);

		return $item;
	}

	public function save(array $data = [])
	{
		$db = $this->getDbo();

		if (!$data)
		{
			$data = [
				'id'         => $this->input->getInt('id', 0),
				'user_id'    => $this->input->getInt('user_id', ''),
				'expiration' => $this->input->getString('expiration', '')
			];
		}

		if (!isset($data['id']))
		{
			$data['id'] = '';
		}

		// Make sure I am not editing myself
		if ($data['user_id'] == get_current_user_id())
		{
			throw new RuntimeException(Language::_('COM_ADMINTOOLS_ERR_TEMPSUPERUSERS_CANTEDITSELF'), 403);
		}

		// Make sure I am not setting an expiration time in the past
		$tz = Wordpress::get_timezone_string();

		$jNow  = new Date();
		$jThen = new Date($data['expiration'], $tz);

		if ($jThen->toUnix() < $jNow->toUnix())
		{
			throw new RuntimeException(Language::_('COM_ADMINTOOLS_ERR_TEMPSUPERUSERS_EXPIRATIONINPAST'), 500);
		}

		$data['expiration'] = $jThen->toSql();

		$data = (object) $data;

		if (!$data->id)
		{
			$db->insertObject('#__admintools_tempsuperusers', $data, 'id');
		}
		else
		{
			$db->updateObject('#__admintools_tempsuperusers', $data, ['id']);
		}

		return $data->id;
	}

	protected function findExistingUser($username)
	{
		$user = get_user_by('user_login', $username);

		if (!$user)
		{
			return 0;
		}

		// Make sure the user is a Super User
		if (Wordpress::getUserAdminLevel($user) < 3)
		{
			throw new RuntimeException(Language::_('COM_ADMINTOOLS_ERR_TEMPSUPERUSERS_NOTSUPER'), 500);
		}

		return $user->ID;
	}
}
