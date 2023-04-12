<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo\View\Remotefiles;


use Awf\Mvc\View;
use Awf\Utils\Template;

class Html extends View
{
	/**
	 * The available remote file actions
	 *
	 * @var  array
	 */
	public $actions = [];

	/**
	 * The capabilities of the remote storage engine
	 *
	 * @var  array
	 */
	public $capabilities = [];

	/**
	 * Total size of the file(s) to download
	 *
	 * @var  int
	 */
	public $total;

	/**
	 * Total size of downloaded file(s) so far
	 *
	 * @var  int
	 */
	public $done;

	/**
	 * Percentage of the total download complete, rounded to the nearest whole number (0-100)
	 *
	 * @var  int
	 */
	public $percent;

	/**
	 * The backup record ID we are downloading back to the server
	 *
	 * @var  int
	 */
	public $id;

	/**
	 * The part number currently being downloaded
	 *
	 * @var  int
	 */
	public $part;

	/**
	 * The fragment of the part currently being downloaded
	 *
	 * @var  int
	 */
	public $frag;

	public function onBeforeListActions()
	{
		Template::addJs('media://js/solo/remotefiles.min.js');

		/** @var \Solo\Model\Remotefiles $model */
		$model = $this->getModel();

		$this->id           = $model->getState('id', -1);
		$this->actions      = $model->getActions($this->id);
		$this->capabilities = $model->getCapabilities($this->id);

		return true;
	}

	public function onBeforeDownloadToServer()
	{
		Template::addJs('media://js/solo/remotefiles.min.js');

		/** @var \Solo\Model\Remotefiles $model */
		$model = $this->getModel();

		$this->setLayout('dlprogress');

		// Get progress bar stats
		$this->total   = $this->container->segment->get('dl_totalsize', 0);
		$this->done    = $this->container->segment->get('dl_donesize', 0);
		$this->percent = ($this->total > 0) ? min(100, (int) (100 * (abs($this->done) / abs($this->total)))) : 0;
		$this->id      = $model->getState('id', 0, 'int');
		$this->part    = $model->getState('part', 0, 'int');
		$this->frag    = $model->getState('frag', 0, 'int');

		return true;
	}
} 
