<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

use Akeeba\AdminTools\Admin\Helper\ConfigManager;
use Akeeba\AdminTools\Admin\Helper\Storage;
use Akeeba\AdminTools\Admin\Model\Advancedwpconfig\Manager;
use Akeeba\AdminTools\Library\Input\Input;
use Akeeba\AdminTools\Library\Mvc\Model\Model;

defined('ADMINTOOLSINC') or die;

class AdvancedWPConfig extends Model
{
	/** @var  object	The current configuration of this feature */
	protected $config = null;

	/** @var  object	The current configuration of this feature	*/
	protected $configKey = 'wpconfig';

	/**
	 * The base name of the configuration file being saved by this feature, e.g. ".htaccess". The file is always saved
	 * in the site's root. Any old files under that name are renamed with a .admintools suffix.
	 *
	 * @var string
	 */
	protected $configFileName = 'wp-config.php';

	/** @var Manager */
	protected $adv_manager;

	public function loadConfiguration()
	{
		if (!is_null($this->adv_manager))
		{
			return $this->adv_manager->getConfigValues();
		}

		$params      = Storage::getInstance();
		$savedConfig = $params->getValue($this->configKey, '');

		if (!empty($savedConfig))
		{
			if (function_exists('base64_encode'))
			{
				$savedConfig = base64_decode($savedConfig);
			}

			$savedConfig = json_decode($savedConfig, true);
		}
		else
		{
			$savedConfig = [];
		}

		// Migrate old post_revisions_value into post_revisions. This should be removed in future releases
		if (isset($savedConfig['post_revisions_value']) && isset($savedConfig['post_revisions']))
		{
			if ($savedConfig['post_revisions'] == 'custom')
			{
				$savedConfig['post_revisions'] = $savedConfig['post_revisions_value'];
			}
		}

		$this->adv_manager = Manager::getInstance($savedConfig);

		return $this->adv_manager->getConfigValues();
	}

	/**
	 * Save the configuration to the database
	 *
	 * @param   object|array  $data           The data to save
	 * @param   bool          $isConfigInput  True = $data is object. False (default) = $data is an array.
	 */
	public function saveConfiguration($data, $isConfigInput = false)
	{
		// First of all load previous configuration
		$this->loadConfiguration();

		// Always cast it as object, as it would happen with json decode
		$this->adv_manager->bind($data);
		$config = (object) $this->adv_manager->getConfigValues();
		$config = json_encode($config);

		// This keeps JRegistry from hapily corrupting our data :@
		if (function_exists('base64_encode') && function_exists('base64_encode'))
		{
			$config = base64_encode($config);
		}

		$params = Storage::getInstance();

		$params->setValue($this->configKey, $config);
		$params->save();
	}

	/**
	 * Make the configuration file and write it to the disk
	 *
	 * @return  bool
	 */
	public function writeConfigFile()
	{
		$configPath = ABSPATH . $this->configFileName;
		$backupPath = ABSPATH . basename($this->configFileName, '.php') . '.admintools.php';

		if (!@copy($configPath, $backupPath))
		{
			return false;
		}

		$configFileContents = $this->makeConfigFile();

		/**
		 * Convert CRLF to LF before saving the file. This would work around an issue with Windows browsers using CRLF
		 * line endings in text areas which would then be transferred verbatim to the output file.
		 */
		$configFileContents = str_replace("\r\n", "\n", $configFileContents);

		if (!@file_put_contents($configPath, $configFileContents))
		{
			return false;
		}

		return true;
	}

	/**
	 * Build the new configuration file, accordingly with the features we saved
	 *
	 * @return bool|int|string
	 */
	public function makeConfigFile()
	{
		// Double check the configuration is actually loaded
		$this->loadConfiguration();

		$configManager = ConfigManager::getInstance();
		$advManager    = $this->adv_manager;

		// Let's query the Advanced Configuration Manager and ask him to gather all the available features and
		// write their values here. By default features will return NULL, meaning that the feature is disabled
		foreach ($advManager->getFeatures() as $feature)
		{
			$configManager->setOption($feature->getConstantName(), $feature->getOptionValue(), $feature->isNamespaced());
		}

		return $configManager->updateFile(false);
	}
}
