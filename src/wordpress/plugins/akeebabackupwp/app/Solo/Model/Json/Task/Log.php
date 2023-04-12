<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo\Model\Json\Task;

use Akeeba\Engine\Factory;
use Awf\Application\Application;
use Awf\Mvc\Model;
use Solo\Model\Json\TaskInterface;
use Solo\Model\Log as LogModel;

/**
 * Get the log contents
 */
class Log implements TaskInterface
{
	/**
	 * Return the JSON API task's name ("method" name). Remote clients will use it to call us.
	 *
	 * @return  string
	 */
	public function getMethodName()
	{
		return 'log';
	}

	/**
	 * Execute the JSON API task
	 *
	 * @param   array  $parameters  The parameters to this task
	 *
	 * @return  mixed
	 *
	 * @throws  \RuntimeException  In case of an error
	 */
	public function execute(array $parameters = [])
	{
		// Get the passed configuration values
		$defConfig = [
			'tag' => 'remote',
		];

		$defConfig = array_merge($defConfig, $parameters);
		$tag       = (int) $defConfig['tag'];

		$container = Application::getInstance()->getContainer();
		/** @var LogModel $model */
		$model = Model::getTmpInstance($container->application_name, 'Log', $container);
		$model->setState('tag', $tag);

		@ob_start();
		$model->echoRawLog(false);

		return @ob_get_clean();
	}
}
