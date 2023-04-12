<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Library\Mvc\Controller\Controller;
use RuntimeException;

defined('ADMINTOOLSINC') or die;

class TempSuperUsers extends Controller
{
	/** @var bool Am I creating a new user or editing an old one? */
	private $newUser = false;

	private $newUserId = 0;

	public function __construct($input)
	{
		parent::__construct($input);

		// Make sure temporary super users cannot access the view
		$this->assertNotTemporary();
	}

	public function onBeforeSave()
	{
		// Triple check that we're truly allowed to do so
		$this->csrfProtection();

		$user_id = $this->input->getInt('user_id', 0);

		if (!$user_id)
		{
			$info = [
				'username' => $this->input->getString('username', 0),
				'password' => $this->input->getString('password', ''),
				'email'    => $this->input->getString('email', ''),
				'name'     => $this->input->getString('name', '')
			];

			// Create or find a user
			/** @var \Akeeba\AdminTools\Admin\Model\TempSuperUsers $model */
			$model     = $this->getModel();

			try
			{
				$user_id   = $model->getUserIdFromInfo($info);
			}
			catch (RuntimeException $e)
			{
				$this->getView()->enqueueMessage($e->getMessage(), 'error')->setTask('browse');
				$this->redirect(ADMINTOOLSWP_URL.'&view='.$this->name);
			}

			$this->newUserId = $user_id;

			$this->input->set('user_id', $user_id);
		}

		$this->assertNotMyself($user_id);
	}

	public function onAfterSave($success)
	{
		// Do nothing if we had a failure
		if (!$success)
		{
			return;
		}

		if ($this->newUserId)
		{
			$wp_user = get_user_by('ID', $this->newUserId);
			$wp_user->set_role($this->input->getString('role', ''));

			$result = wp_update_user($wp_user);

			if ($result instanceof \WP_Error)
			{
				$this->getView()->enqueueMessage($result->get_error_message(), 'error')->setTask('browse');
				$this->redirect(ADMINTOOLSWP_URL.'&view='.$this->name);
			}
		}
	}

	/**
	 * Asserts that I am not trying to modify my own user.
	 *
	 * If you do not specify the user ID being edited / created we'll figure it out from the request using the model.
	 *
	 * @param   int|null  $editingID  The ID of the user being edited.
	 *
	 * @since   5.3.0
	 */
	protected function assertNotMyself($editingID = null)
	{
		$id = $editingID;

		if (is_null($editingID))
		{
			$id = $this->input->getInt('user_id', 0);
		}

		$myId = get_current_user_id();

		if ($id == $myId)
		{
			throw new \RuntimeException(Language::sprintf('COM_ADMINTOOLS_ERR_TEMPSUPERUSERS_CANTEDITSELF'), 403);
		}
	}

	/**
	 * Asserts that I am not a temporary Super User myself
	 *
	 * @since   5.3.0
	 */
	protected function assertNotTemporary()
	{
		/** @var \Akeeba\AdminTools\Admin\Model\TempSuperUsers $model */
		$model = $this->getModel();
		$myId  = get_current_user_id();

		// Try to find a temporary super user with my own ID
		if (!$model->getItem(['user_id' => $myId]))
		{
			// Could not find a temporary super user that's myself. Good!
			return;
		}

		// Uh oh, I am a temporary Super User.
		throw new RuntimeException(Language::sprintf('COM_ADMINTOOLS_ERR_TEMPSUPERUSERS_UNAVAILABLETOTEMP'), 403);
	}
}
