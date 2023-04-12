<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\View\ScanAlerts;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\Params;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use Akeeba\AdminTools\Admin\Model\ScanAlerts;
use Akeeba\AdminTools\Admin\Model\Scans;

class Html extends \Akeeba\AdminTools\Library\Mvc\View\Html
{
	/** @var  \DateTime The start date/time of the scan */
	public $scanDate;

	/** @var  bool      Should I be generating diffs for changed files? */
	public $generateDiff;

	/** @var  string    Threat index (high, medium, low, nonw) */
	public $threatindex = 'high';

	/** @var  string    File status */
	public $fstatus = 'modified';

	/** @var  bool      Is this file suspicious? */
	public $suspiciousFile = false;

	/** @var  array     The return array from Akeeba Engine while the scan is in progress */
	public $retarray;

	protected function onBeforeEdit()
	{
		$this->setLayout('form');

		// Load highlight.js
		wp_enqueue_script('admintoolswp_highlightjs', '//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.2.0/highlight.min.js');
		wp_enqueue_style('admintoolswp_highlightcss', '//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.2.0/styles/default.min.css');

		/** @var ScanAlerts $model */
		$model = $this->getModel();
		$id    = $this->input->getInt('id', 0);

		$this->item = $model->getItem($id);

		$params = Params::getInstance();

		$this->generateDiff = $params->getValue('scandiffs', false);

		/** @var Scans $scanModel */
		$scanModel = $this->getModel('Scans');
		$scan = $scanModel->getItem($this->item->scan_id);

		$this->scanDate = new \DateTime($scan->scanstart);

		$this->item->newfile    = empty($this->item->diff);
		$this->item->suspicious = substr($this->item->diff, 0, 21) == '###SUSPICIOUS FILE###';

		// Calculate the threat index
		if ($this->item->threat_score == 0)
		{
			$this->threatindex = 'none';
		}
		elseif ($this->item->threat_score < 10)
		{
			$this->threatindex = 'low';
		}
		elseif ($this->item->threat_score < 100)
		{
			$this->threatindex = 'medium';
		}

		// File status
		if ($this->item->newfile)
		{
			$this->fstatus = 'new';
		}
		elseif ($this->item->suspicious)
		{
			$this->fstatus = 'suspicious';
		}

		// Should I render a diff?
		if (!empty($this->item->diff))
		{
			$diffLines = explode("\n", $this->item->diff);
			$firstLine = array_shift($diffLines);

			if ($firstLine == '###SUSPICIOUS FILE###')
			{
				$this->suspiciousFile = true;
				$this->item->diff     = '';
			}
			elseif ($firstLine == '###MODIFIED FILE###')
			{
				$this->item->diff = '';
			}

			if ($this->suspiciousFile && (count($diffLines) > 4))
			{
				array_shift($diffLines);
				array_shift($diffLines);
				array_shift($diffLines);
				array_shift($diffLines);

				$this->item->diff = implode("\n", $diffLines);
			}

			unset($diffLines);
		}

		Wordpress::enqueueScript('scanalert.js', array('jquery-ui-accordion'));
	}

	protected function onBeforeDisplay()
	{
		/** @var ScanAlerts $model */
		$model = $this->getModel();

		// Items are already filtered by scan_id since it's in the request
		$this->items = $model->getItems();
		$this->total = $model->getTotal();
		$this->limitstart = $this->input->getInt('limitstart', 0);
	}

	public function onBeforePrintlist()
	{
		$this->setLayout('print');

		// Clean any previous output
		@ob_clean();

		/** @var ScanAlerts $model */
		$model = $this->getModel();
		/** @var Scans $scanModel */
		$scanModel = $this->getModel('Scans');

		$this->scan  = $scanModel->getItem($this->input->getInt('scan_id', 0));

		// Items are already filtered by scan_id since it's in the request
		$this->items = $model->getItems(true);
	}
}
