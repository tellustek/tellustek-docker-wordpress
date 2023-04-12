<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo\View\S3import;


use Awf\Mvc\View;
use Awf\Utils\Template;
use Solo\Model\S3import;

class Html extends View
{
	public $s3access;
	public $s3secret;
	public $buckets;
	public $bucketSelect;
	public $contents;
	public $root;
	public $crumbs;
	public $total;
	public $done;
	public $percent;
	public $total_parts;
	public $current_part;

	public function onBeforeMain()
	{
		Template::addJs('media://js/solo/s3import.js', $this->container->application);

		/** @var S3import $model */
		$model    = $this->getModel();
		$router   = $this->getContainer()->router;
		$document = $this->container->application->getDocument();

		$model->getS3Credentials();

		// Assign variables
		$this->s3access     = $model->getState('s3access');
		$this->s3secret     = $model->getState('s3secret');
		$this->buckets      = $model->getBuckets();
		$this->bucketSelect = $model->getBucketsDropdown();
		$this->contents     = $model->getContents();
		$this->root         = $model->getState('folder', '', 'raw');
		$this->crumbs       = $model->getCrumbs();

		// Script options
		$document->addScriptOptions('akeeba.S3Import.accessKey', $this->s3access);
		$document->addScriptOptions('akeeba.S3Import.secretKey', $this->s3secret);
		$document->addScriptOptions('akeeba.S3Import.importURL', $router->route('index.php?view=S3import&task=dltoserver&part=-1&frag=-1&layout=downloading'));

		return true;
	}

	public function onBeforeDltoserver()
	{
		Template::addJs('media://js/solo/s3import.js', $this->container->application);

		$this->setLayout('downloading');
		$model    = $this->getModel();
		$router   = $this->getContainer()->router;
		$document = $this->container->application->getDocument();

		$total = $model->getState('totalsize', 0, 'int');
		$done  = $model->getState('donesize', 0, 'int');
		$part  = $model->getState('part', 0, 'int') + 1;
		$parts = $model->getState('totalparts', 0, 'int');

		$percent = 0;

		if ($total > 0)
		{
			$percent = (int) (100 * ($done / $total));
			$percent = max(0, $percent);
			$percent = min($percent, 100);
		}

		$this->total        = $total;
		$this->done         = $done;
		$this->percent      = $percent;
		$this->total_parts  = $parts;
		$this->current_part = $part;

		// Add an immediate redirection URL as a script option
		$step     = $model->getState('step', 1, 'int') + 1;
		$location = $router->route('index.php?view=s3import&layout=downloading&task=dltoserver&step=' . $step);
		$document->addScriptOptions('akeeba.S3Import.autoRedirectURL', $location);

		return true;
	}
} 
