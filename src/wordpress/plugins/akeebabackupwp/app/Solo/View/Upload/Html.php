<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo\View\Upload;


use Awf\Mvc\View;
use Awf\Utils\Template;

class Html extends View
{
	/**
	 * ID of the record to reupload to remote torage
	 *
	 * @var  int
	 */
	public $id = 0;

	/**
	 * Total number of parts which have to be uploaded
	 *
	 * @var  int
	 */
	public $parts = 0;

	/**
	 * Current part being uploaded
	 *
	 * @var  int
	 */
	public $part = 0;

	/**
	 * Current fragment of the part being uploaded
	 *
	 * @var  int
	 */
	public $frag = 0;

	/**
	 * Are we done? 0/1
	 *
	 * @var  int
	 */
	public $done = 0;

	/**
	 * Is there an error? 0/1
	 *
	 * @var  int
	 */
	public $error = 0;

	/**
	 * Error message to display
	 *
	 * @var  string
	 */
	public $errorMessage = '';

	public function onBeforeUpload()
	{
		Template::addJs('media://js/solo/upload.js', $this->container->application);

		$this->setLayout('uploading');

		if ($this->done)
		{
			$this->setLayout('done');
		}
		elseif ($this->error)
		{
			$this->setLayout('error');
		}

		return true;
	}

	public function onBeforeCancelled()
	{
		Template::addJs('media://js/solo/upload.js', $this->container->application);

		$this->setLayout('error');

		return true;
	}

	public function onBeforeStart()
	{
		Template::addJs('media://js/solo/upload.js', $this->container->application);

		$this->setLayout('default');

		if ($this->done)
		{
			$this->setLayout('done');
		}
		elseif ($this->error)
		{
			$this->setLayout('error');
		}

		return true;
	}
}
