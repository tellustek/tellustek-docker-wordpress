<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo\Controller;

use Akeeba\Engine\Platform;
use Akeeba\Engine\Util\Complexify;
use Awf\Input\Input;
use Solo\Model\Json\Task;

/**
 * API version
 *
 * 400: First JSON API v2 implementation
 */
if (!defined('AKEEBA_JSON_API_VERSION'))
{
	define('AKEEBA_JSON_API_VERSION', 400);
}

class Api extends ControllerDefault
{
	/**
	 * Secret Key (cached for quicker retrieval)
	 *
	 * @var   null|string
	 * @since 7.4.0
	 */
	private $key = null;

	/**
	 * Always execute the 'json' task
	 *
	 * @param   string $task
	 *
	 * @return  boolean|null
	 */
	public function execute($task)
	{
		$this->input->set('task', 'main');
		$task = 'main';

		return parent::execute($task);
	}

	public function main()
	{
		if (!defined('AKEEBA_BACKUP_ORIGIN'))
		{
			define('AKEEBA_BACKUP_ORIGIN', 'json');
		}

		$outputBuffering = function_exists('ob_start') && function_exists('ob_end_clean');

		// Use the model to parse the JSON message
		if ($outputBuffering)
		{
			@ob_start();
		}

		try
		{
			if (!$this->verifyKey())
			{
				throw new \RuntimeException("Access denied", 503);
			}

			$httpVerb = $this->input->getMethod() ?? 'GET';

			switch ($httpVerb)
			{
				case 'GET':
					$method = $this->input->get->getCmd('method', '');
					$input  = new Input($_GET);
					break;

				case 'POST':
					$method = $this->input->post->getCmd('method', '');
					$input  = new Input($_POST);
					break;

				default:
					throw new \RuntimeException("Invalid HTTP method {$httpVerb}", 405);
					break;
			}

			$taskHandler = new Task();

			$result = [
				'status' => 200,
				'data' => $taskHandler->execute($method, $input->getData())
			];
		}
		catch (\Exception $e)
		{
			$result = [
				'status' => $e->getCode(),
				'data'   => $e->getMessage(),
			];
		}

		if ($outputBuffering)
		{
			@ob_end_clean();
		}

		$this->sendResponse($result);
	}

	/**
	 * Send a JSON response when format=html or anything other than json
	 *
	 * @param   \JsonSerializable|array  $result
	 *
	 * @throws \Exception
	 *
	 * @since  7.4.0
	 */
	private function sendResponse($result): void
	{
		$jsonOptions = (defined('AKEEBADEBUG') && AKEEBADEBUG) ? JSON_PRETTY_PRINT : 0;

		if (defined('WPINC') && wp_doing_ajax())
		{
			wp_send_json($result, 200, $jsonOptions);
		}

		// Disable caching
		@header('Expires: Wed, 17 Aug 2005 00:00:00 GMT', true);
		@header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0', true);
		@header('Pragma: no-cache', true);

		// JSON content
		@header('Content-Type: application/json; charset=utf-8', true);
		@header('Content-Disposition: attachment; filename="joomla.json"', true);

		echo json_encode($result, $jsonOptions);

		$this->container->application->close();
	}

	/**
	 * Verifies the Secret Key (API token)
	 *
	 * @return  bool
	 * @since   7.4.0
	 */
	private function verifyKey(): bool
	{
		// Is the JSON API enabled?
		if (Platform::getInstance()->get_platform_configuration_option('jsonapi_enabled', 0) != 1)
		{
			return false;
		}

		// Is the key secure enough?
		$validKey = $this->serverKey();

		if (empty($validKey) || empty(trim($validKey)) || !Complexify::isStrongEnough($validKey, false))
		{
			return false;
		}

		/**
		 * Get the API authentication token. There are two sources
		 * 1. X-Akeeba-Auth header (preferred, overrides all others)
		 * 2. the _akeebaAuth GET parameter
		 */
		$authSource = $this->input->server->getString('HTTP_X_AKEEBA_AUTH', null);

		if (is_null($authSource))
		{
			$authSource = $this->input->get->getString('_akeebaAuth', null);
		}

		// No authentication token? No joy.
		if (empty($authSource) || !is_string($authSource) || empty(trim($authSource)))
		{
			return false;
		}

		return hash_equals($validKey, $authSource);
	}

	/**
	 * Get the server key, i.e. the Secret Word for the front-end backups and JSON API
	 *
	 * @return  mixed
	 *
	 * @since   7.4.0
	 */
	private function serverKey()
	{
		if (is_null($this->key))
		{
			$this->key = Platform::getInstance()->get_platform_configuration_option('frontend_secret_word', '');
		}

		return $this->key;
	}
} 
