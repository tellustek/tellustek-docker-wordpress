<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Solo\Model;

use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use Akeeba\Engine\Util\RandomValue;
use Akeeba\Engine\Util\Transfer;
use Awf\Download\Download;
use Awf\Mvc\Model;
use Awf\Session\Segment;
use Awf\Text\Text;
use Awf\Uri\Uri;
use Countable;
use Exception;
use RuntimeException;
use Solo\Model\Exception\TransferFatalError;
use Solo\Model\Exception\TransferIgnorableError;

class Transfers extends Model
{
	/**
	 * Caches the domain names and whether they can be resolved by DNS
	 *
	 * @var   array
	 * @since 7.6.4
	 */
	private static $domainResolvable = [];

	/**
	 * Get the information for the latest backup
	 *
	 * @param   $profileID  int|null  The profile ID for which to get the latest backup. Set to null to search all profiles.
	 *
	 * @return   array|null  An array of backup record information or null if there is no usable backup for site transfer
	 */
	public function getLatestBackupInformation($profileID = null)
	{
		// Initialise
		$ret = null;

		$db = Factory::getDatabase();

		/** @var Manage $model */
		$model = Model::getTmpInstance($this->container->application_name, 'Manage', $this->container);
		$model->savestate(0);
		$model->setState('limitstart', 0);
		$model->setState('limit', 1);

		if ($profileID > 0)
		{
			$model->setState('profile_id', $profileID);
		}

		$backups = $model->getStatisticsListWithMeta(false, null, $db->qn('id') . ' DESC');

		// No valid backups? No joy.
		if (empty($backups))
		{
			return $ret;
		}

		// Get the latest backup
		$backup = array_shift($backups);

		// If it's not stored on the server (e.g. remote backup), no joy.
		if ($backup['meta'] != 'ok')
		{
			return $ret;
		}

		// If it's not a full site backup, no joy.
		if ($backup['type'] != 'full')
		{
			return $ret;
		}

		return $backup;
	}

	/**
	 * Returns the amount of space required on the target server. The two array keys are
	 * size		In bytes
	 * string	Pretty formatted, user-friendly string
	 *
	 * @return  array
	 */
	public function getApproximateSpaceRequired()
	{
		$backup = $this->getLatestBackupInformation();

		if (is_null($backup))
		{
			return [
				'size'   => 0,
				'string' => '0.00 KB'
			];
		}

		$approximateSize = 2.5 * (float) $backup['size'];

		$unit	 = array('b', 'KB', 'MB', 'GB', 'TB', 'PB');

		return [
			'size'   => $approximateSize,
			'string' => @round($approximateSize / (1024 ** ($i = floor(log($approximateSize, 1024)))), 2) . ' ' . $unit[$i]
		];
	}

	/**
	 * Cleans up a URL and makes sure it is a valid-looking URL
	 *
	 * @param   string  $url  The URL to check
	 *
	 * @return  array  status [ok, invalid, same, notexists] (check status); url (the cleaned URL)
	 */
	public function checkAndCleanUrl($url)
	{
		// Initialise
		$result = array(
			'status'	=> 'ok',
			'url'		=> $url
		);

		// Am I missing the protocol?
		if (strpos($url, '://') === false)
		{
			$url = 'http://' . $url;
		}

		$result['url'] = $url;

		// Verify that it is an HTTP or HTTPS URL.
		$uri = Uri::getInstance($url);
		$protocol = $uri->getScheme();

		if (!in_array($protocol, array('http', 'https')))
		{
			$result['status'] = 'invalid';

			return $result;
		}

		// Verify we are not restoring to the same site we are backing up from
		$path = $this->simplifyPath($uri->getPath());
		$uri->setPath('/' . $path);

		$siteUri = Uri::getInstance();

		if ($siteUri->getHost() == $uri->getHost())
		{
			$sitePath = $this->simplifyPath($siteUri->getPath());

			if ($sitePath == $path)
			{
				$result['status'] = 'same';

				return $result;
			}
		}

		$result['url'] = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port', 'path'));

		// Verify we can reach the domain. Since it can be an IP we check both name to IP and IP to name.
		$host = $uri->getHost();

		if (function_exists('idn_to_ascii'))
		{
			$host = idn_to_ascii($host);
		}

		$isValid = ($siteUri->getHost() == $uri->getHost()) || ($host == 'localhost') || ($host == '127.0.0.1') || (($host !== false) && checkdnsrr($host, 'A'));

		// Sometimes we have a domain name without a DNS record which *can* be accessed locally, e.g. through the hosts
		// file. We have to cater for that, just in case...
		if (!$isValid)
		{
			$options = [];
			$proxyParams = Platform::getInstance()->getProxySettings();

			if ($proxyParams['enabled'])
			{
				$options['proxy'] = [
					'host' => $proxyParams['host'],
					'port' => $proxyParams['port'],
					'user' => $proxyParams['user'],
					'pass' => $proxyParams['pass'],
				];
			}

			$download = new Download($this->container);
			$download->setAdapterOptions($options);
			$dummy = $download->getFromURL($uri->toString());

			$isValid = $dummy !== false;
		}

		// Sometimes just the SSL certificate is wrong. Let's give it a go.
		if (!$isValid)
		{
			$dummy = $this->httpGet($uri->toString(), [], 5);

			$isValid = !empty($dummy);
		}

		if (!$isValid)
		{
			$result['status'] = 'notexists';

			return $result;
		}

		// All checks pass
		return $result;
	}

	/**
	 * Tries to simplify a server path to get the site's root. It can handle most forms on non-SEF and non-rewrite SEF
	 * URLs (as in index.php?foo=bar, something.php/this/is?completely=nuts#ok). It can't fix stupid but it tries really
	 * bloody hard to.
	 *
	 * @param   string  $path  The path to simplify. We *expect* this to contain nonsense.
	 *
	 * @return  string  The scrubbed clean URL, hopefully leading to the site's root.
	 */
	private function simplifyPath($path)
	{
		$path = ltrim($path, '/');

		if (empty($path))
		{
			return $path;
		}

		// Trim out anything after a .php file (including the .php file itself)
		if (substr($path, -1) != '/')
		{
			$parts = explode('/', $path);
			$newParts = array();

			foreach ($parts as $part)
			{
				if (substr($part, -4) == '.php')
				{
					break;
				}

				$newParts[] = $part;
			}

			$path = implode('/', $newParts);
		}

		if (substr($path, -13) == 'administrator')
		{
			$path = substr($path, 0, -13);
		}

		return $path;
	}

	/**
	 * Determines the status of FTP, FTPS and SFTP support. The returned array has two keys 'supported' and 'firewalled'
	 * each one being an array. You want the protocol to has its 'supported' value set to true and its 'firewalled'
	 * value set to false. This would mean that the server supports this protocol AND does not block outbound
	 * connections over this protocol.
	 *
	 * @return array
	 */
	public function getFTPSupport()
	{
		// Initialise
		$result = array(
			'supported'  => array(
				'ftpcurl'  => false,
				'ftpscurl' => false,
				'sftpcurl' => false,
				'ftp'      => false,
				'ftps'     => false,
				'sftp'     => false,
			),
			'firewalled' => array(
				'ftpcurl'  => false,
				'ftpscurl' => false,
				'sftpcurl' => false,
				'ftp'      => false,
				'ftps'     => false,
				'sftp'     => false
			)
		);

		// Necessary functions for each connection method
		$supportChecks = array(
			'ftpcurl'	=> array('curl_init', 'curl_exec', 'curl_setopt', 'curl_errno', 'curl_error'),
			'ftpscurl'	=> array('curl_init', 'curl_exec', 'curl_setopt', 'curl_errno', 'curl_error'),
			'sftpcurl'	=> array('curl_init', 'curl_exec', 'curl_setopt', 'curl_errno', 'curl_error'),
			'ftp'	    => array('ftp_connect', 'ftp_login', 'ftp_close', 'ftp_chdir', 'ftp_mkdir', 'ftp_pasv', 'ftp_put', 'ftp_delete'),
			'ftps'	    => array('ftp_ssl_connect', 'ftp_login', 'ftp_close', 'ftp_chdir', 'ftp_mkdir', 'ftp_pasv', 'ftp_put', 'ftp_delete'),
			'sftp'	    => array('ssh2_connect', 'ssh2_auth_password', 'ssh2_auth_pubkey_file', 'ssh2_sftp', 'ssh2_exec', 'ssh2_sftp_unlink', 'ssh2_sftp_stat', 'ssh2_sftp_mkdir')
		);

		// Determine which connection methods are supported
		$supported = array();

		foreach ($supportChecks as $protocol => $functions)
		{
			$supported[$protocol] = true;

			foreach ($functions as $function)
			{
				if (!function_exists($function))
				{
					$supported[$protocol] = false;

					break;
				}
			}
		}

		$result['supported'] = $supported;

		// Check firewall settings -- Disabled because the 3PD test server got clogged :(
		/**
		$result['firewalled'] = array(
			'ftp'      => !$result['supported']['ftp'] ? false : Transfer\Ftp::isFirewalled(),
			'ftpcurl'  => !$result['supported']['ftp'] ? false : Transfer\FtpCurl::isFirewalled(),
			'ftps'     => !$result['supported']['ftps'] ? false : Transfer\Ftp::isFirewalled(array('ssl' => true)),
			'ftpscurl' => !$result['supported']['ftps'] ? false : Transfer\FtpCurl::isFirewalled(array('ssl' => true)),
			'sftp'     => !$result['supported']['sftp'] ? false : Transfer\Sftp::isFirewalled(),
			'sftpcurl' => !$result['supported']['sftp'] ? false : Transfer\SftpCurl::isFirewalled(),
		);
		/**/

		return $result;
	}

	/**
	 * Checks the FTP connection parameters
	 *
	 * @param   array  $config  FTP/SFTP connection details
	 *
	 * @throws  RuntimeException
	 */
	public function testConnection(array $config)
	{
		/** @var Transfer\TransferInterface $connector */
		$connector = $this->getConnector($config);

		// Is it the same site we are restoring from? It is if the configuration.php exists and has the same contents as
		// the one I read from our server.
		$this->checkIfSameSite($connector);

		// Only perform those checks if I'm not forcing the transfer
		if (!$config['force'])
		{
			// Check if there's a special file in this directory, e.g. .htaccess, php.ini, .user.ini or web.config.
			$this->checkIfHasSpecialFile($connector);

			// Check if there's another site present in this directory
			$this->checkIfExistingSite($connector);
		}

		// Does it match the URL to the site?
		$this->checkIfMatchesUrl($connector);
	}

    /**
     * Upload Kickstart, our extra script and check that the target server fullfills our criteria
     *
     * @param   array $config FTP/SFTP connection details
     *
     * @throws RuntimeException
     * @throws Exception
     */
	public function initialiseUpload(array $config)
	{
		/** @var Transfer\TransferInterface $connector */
		$connector = $this->getConnector($config);

		// Can I upload Kickstart and my extra script?
		$files = array(
			APATH_ROOT . '/Solo/assets/installers/kickstart.txt'  => 'kickstart.php',
			APATH_ROOT . '/Solo/assets/installers/kickstart.transfer.php' => 'kickstart.transfer.php'
		);

		$createdFiles = array();
		$transferredSize = 0;
		$transferTime = 0;

		try
		{
			foreach ($files as $localFile => $remoteFile)
			{
				$start = microtime(true);
				$connector->upload($localFile, $connector->getPath($remoteFile));
				$end = microtime(true);
				$createdFiles[] = $remoteFile;
				$transferredSize += filesize($localFile);
				$transferTime += $end - $start;
			}
		}
		catch (Exception $e)
		{
			// An upload failed. Remove existing files.
			$this->removeRemoteFiles($connector, $createdFiles, true);

			throw new RuntimeException(Text::_('COM_AKEEBA_TRANSFER_ERR_CANNOTUPLOADKICKSTART'));
		}

		// Get the transfer speed between the two servers in bytes / second
		$transferSpeed = $transferredSize / $transferTime;

		try
		{
			$connector->mkdir($connector->getPath('kicktemp'), 0777);
		}
		catch (Exception $e)
		{
			// Don't sweat if we can't create our temporary directory.
		}

		// Can I run Kickstart and my extra script?
		try
		{
			$this->checkRemoteServerEnvironment($config['force']);
		}
		catch (Exception $e)
		{
			$this->removeRemoteFiles($connector, $createdFiles, true);

			throw $e;
		}

		// Get the lowest maximum execution time between our local and remote server
		$remoteTimeout = $this->container->segment->get('transfer.remoteTimeLimit', 5);
		$localTimeout  = 5;

		if (function_exists('ini_get'))
		{
			$localTimeout = ini_get("max_execution_time");
		}

		$timeout = min($localTimeout, $remoteTimeout);

		if ($localTimeout == 0)
		{
			$timeout = $remoteTimeout;
		}
		elseif ($remoteTimeout == 0)
		{
			$timeout = $localTimeout;
		}

		if ($timeout == 0)
		{
			$timeout = 5;
		}

		// Get the maximimum transfer size, rounded down to 512K
		$maxTransferSize = $transferSpeed * $timeout;
		$maxTransferSize = floor($maxTransferSize / 524288) * 524288;

		if ($maxTransferSize == 0)
		{
			$maxTransferSize = 524288;
		}

		/**
		 * We never go above a maximum transfer size that depends on the server memory setting and the maximum remote
		 * upload size (minus 10Kb for overhead data)
		 */
		// Maximum chunk size determined by local server's memory constraints
		$chunkSizeLimit  = $this->getMaxChunkSize();
		// Chunk size selected by the user
		$maxUploadLimit  = $this->container->segment->get('transfer.uploadLimit', 5242880) - 10240;
		// Maximum chunk size determined by the remote server
		$userChunkSize   = $this->container->segment->get('transfer.chunkSize', 5242880) - 10240;
		// Calculated optimum chunk size (maxTransferSize is calculated by server-to-server speed limits)
		$maxTransferSize = min($maxUploadLimit, $userChunkSize, $maxTransferSize, $chunkSizeLimit);

		/**
		 * A little explanation for "$maxUploadLimit / 4" below. We are uploading binary data which gets encoded as
		 * form data. The integer part is a rough estimation of the size discrepancy between raw and encoded data.
		 */
		if ($config['chunkMode'] == 'post')
		{
			$maxTransferSize = min(floor($maxUploadLimit / 4), $maxTransferSize, $chunkSizeLimit);
		}

		// Save the optimal transfer size in the session
		$this->container->segment->set('transfer.fragSize', $maxTransferSize);
	}

    /**
     * Upload the next fragment
     *
     * @param   array $config FTP/SFTP connection details
     *
     * @return  array
     *
     * @throws RuntimeException
     */
	public function uploadChunk(array $config)
	{
		$ret = array(
			'result'    => true,
			'done'      => false,
			'message'   => '',
			'totalSize' => 0,
			'doneSize'  => 0
		);

		// Get information from the session
		$session    = $this->container->segment;
		$fragSize   = $session->get('transfer.fragSize', 5242880);
		$backup     = $session->get('transfer.lastBackup', array());
		$totalSize  = $session->get('transfer.totalSize', 0);
		$doneSize   = $session->get('transfer.doneSize', 0);
		$part       = $session->get('transfer.part', -1);
		$frag       = $session->get('transfer.frag', -1);

		// Do I need to update the total size?
		if (!$totalSize)
		{
			$totalSize = $backup['total_size'];
			$session->set('transfer.totalSize', $totalSize);
		}

		$ret['totalSize'] = $totalSize;

		// First fragment of a new part
		if ($frag == -1)
		{
			$frag = 0;
			$part++;
		}

		/**
		 * If the backup is single part then $backup['multipart'] is 0. This means that the next if-block will report
		 * that the transfer is done. In these cases we have to convert $backup['multipart'] to 1 to let the upload
		 * actually run at all.
		 */
		if ($backup['multipart'] == 0)
		{
			$backup['multipart'] = 1;
		}

		// If I'm past the last part I'm done
		if ($part >= $backup['multipart'])
		{
			// We are done
			$ret['done'] = true;
			return $ret;
		}

		// Get the information for this part
		$fileName = $this->getPartFilename($backup['absolute_path'], $part);
		$fileSize  = filesize($fileName);

		$intendedSeekPosition = $fragSize * $frag;

		// I am trying to seek past EOF. Oops. Upload the next part.
		if ($intendedSeekPosition >= $fileSize)
		{
			$session->set('transfer.frag', -1);

			return $this->uploadChunk($config);
		}

		// Open the part
		$fp = @fopen($fileName, 'r');

		if ($fp === false)
		{
			$ret['result'] = false;
			$ret['message'] = Text::sprintf('COM_AKEEBA_TRANSFER_ERR_CANNOTREADLOCALFILE', $fileName);

			return $ret;
		}

		// Seek to position
		if (fseek($fp, $intendedSeekPosition) == -1)
		{
			@fclose($fp);

			$ret['result'] = false;
			$ret['message'] = Text::sprintf('COM_AKEEBA_TRANSFER_ERR_CANNOTREADLOCALFILE', $fileName);

			return $ret;
		}

		// Read the data
		$data = fread($fp, $fragSize);
		$doneSize += strlen($data);
		$ret['doneSize'] = $doneSize;
		$session->set('transfer.doneSize', $doneSize);

		// Upload the data
		$session->set('transfer.frag', $frag);

		try
		{
			switch ($config['chunkMode'])
			{
				case 'post':
					$dataLength = $this->uploadUsingPost($fileName, $data, $session);
					break;

				case 'chunked':
				default:
					$dataLength = $this->uploadUsingChunked($fileName, $data, $session, $config);
					break;
			}
		}
		// A finally{} block is what we really need but it's not supported until PHP 5.5 and I'm stuck supporting 5.3 :(
		catch (RuntimeException $e)
		{
			// Close the part
			fclose($fp);

			// Rethrow the exception
			throw $e;
		}

		// Close the part
		fclose($fp);

		// Update the session data
		$session->set('transfer.fragSize', $fragSize);
		$session->set('transfer.totalSize', $totalSize);
		$session->set('transfer.doneSize', $doneSize);
		$session->set('transfer.part', $part);
		$session->set('transfer.frag', ++$frag);

		// Did I go past EOF? Then on to the next part
		$intendedSeekPosition += $dataLength;

		if ($intendedSeekPosition >= $fileSize)
		{
			$session->set('transfer.frag', -1);
			$session->set('transfer.part', ++$part);
		}

		// Did I reach the last part? Then I'm done
		if ($part >= $backup['multipart'])
		{
			// We are done
			$ret['done'] = true;
		}

		return $ret;
	}

	/**
	 * Reset the upload information. Required to start over.
	 *
	 * @return  void
	 */
	public function resetUpload()
	{
		$session = $this->container->segment;

		$session->set('transfer.totalSize', 0);
		$session->set('transfer.doneSize', 0);
		$session->set('transfer.part', -1);
		$session->set('transfer.frag', -1);
	}

	/**
	 * Gets the TransferInterface connector object based on the $config configuration parameters array
	 *
	 * @param   array  $config  The configuration array with the FTP/SFTP connection information
	 *
	 * @return  Transfer\TransferInterface
	 *
	 * @throws  RuntimeException
	 */
	private function getConnector(array $config)
	{
		switch ($config['method'])
		{
			case 'sftp':
				$connector = new Transfer\Sftp($config);
				break;

			case 'sftpcurl':
				$connector = new Transfer\SftpCurl($config);
				break;

			case 'ftpcurl':
			case 'ftpscurl':
				$connector = new Transfer\FtpCurl($config);
				break;

			default:
				$connector = new Transfer\Ftp($config);
				break;
		}

		return $connector;
	}

    /**
     * Checks if the remote site is the same as the site we are running the wizard from.
     *
     * @param   Transfer\TransferInterface $connector
     */
	private function checkIfSameSite(Transfer\TransferInterface $connector)
	{
        // TODO Is really possible to check if the site is the same?
		$myConfiguration = @file_get_contents(APATH_ROOT . '/version.php');

		if ($myConfiguration === false)
		{
			return;
		}

		try
		{
			$otherConfiguration = $connector->read($connector->getPath('version.php'));
		}
		catch (Exception $e)
		{
			// File not found. No harm done.

			return;
		}

		if ($otherConfiguration == $myConfiguration)
		{
			throw new RuntimeException(Text::_('COM_AKEEBA_TRANSFER_ERR_SAMESITE'));
		}
	}

	/**
	 * Check if there's a special file which might prevent site transfer from taking place.
	 *
	 * @param   Transfer\TransferInterface  $connector
	 */
	private function checkIfHasSpecialFile(Transfer\TransferInterface $connector)
	{
		$possibleFiles = array('.htaccess', 'web.config', 'php.ini', '.user.ini');

		foreach ($possibleFiles as $file)
		{
			try
			{
				$fileContents = $connector->read($connector->getPath($file));
			}
			catch (Exception $e)
			{
				// File not found. No harm done.
				continue;
			}

			if (empty($fileContents))
			{
				continue;
			}

			throw new TransferIgnorableError(Text::sprintf('COM_AKEEBA_TRANSFER_ERR_HTACCESS', $file));
		}
	}

	/**
	 * Check if there's an existing site
	 *
	 * @param   Transfer\TransferInterface  $connector
	 */
	private function checkIfExistingSite(Transfer\TransferInterface $connector)
	{
		/**
		 * I run into a PHP bug. When we try to read 'wordpress/index.php' over FTP to determine if it exists we end up
		 * with the folder "wordpress" being created. I have only been able to reproduce with with VSFTPd. The VSFTPd
		 * log claims there is only an unsuccessful read operation. Why the folder is create is a mystery, but I have to
		 * remove it anyway. I know, right?
		 */
		// $possibleFiles = array('index.php', 'wordpress/index.php');
		$possibleFiles = array('index.php');

		foreach ($possibleFiles as $file)
		{
			try
			{
				$fileContents = $connector->read($connector->getPath($file));
			}
			catch (Exception $e)
			{
				// File not found. No harm done.
				continue;
			}

			if (empty($fileContents))
			{
				continue;
			}

			throw new TransferIgnorableError(Text::_('COM_AKEEBA_TRANSFER_ERR_EXISTINGSITE'));
		}
	}

	/**
	 * Check if the connection matches the site's stated URL
	 *
	 * @param   Transfer\TransferInterface  $connector
	 */
	private function checkIfMatchesUrl(Transfer\TransferInterface $connector)
	{
		$sourceFile = APATH_SITE . '/media/logo/' . $this->container->iconBaseName . '-16.png';

		// Try to upload the file
		try
		{
			$connector->upload($sourceFile, $connector->getPath(basename($sourceFile)));
		}
		catch (Exception $e)
		{
			$errorMessage = Text::sprintf('COM_AKEEBA_TRANSFER_ERR_CANNOTUPLOADTESTFILE', basename($sourceFile));

			$errorMessage .= "  &mdash;  [ " . $e->getMessage() . ' ]';

			throw new RuntimeException($errorMessage);
		}

		// Try to fetch the file over HTTP
		$session = $this->container->segment;
		$url = $session->get('transfer.url', '');
		$url = rtrim($url, '/');

		$options = [];
		$proxyParams = Platform::getInstance()->getProxySettings();

		if ($proxyParams['enabled'])
		{
			$options['proxy'] = [
				'host' => $proxyParams['host'],
				'port' => $proxyParams['port'],
				'user' => $proxyParams['user'],
				'pass' => $proxyParams['pass'],
			];
		}

		$downloader = new Download($this->container);
		$downloader->setAdapterOptions($options);
		$wrongSSL   = false;

		try
		{
			$data = $downloader->getFromURL($url . '/' . basename($sourceFile));
		}
		catch (Exception $e)
		{
			$data = false;
		}

		/**
		 * The download of the test file failed. This can mean that the (S)FTP directory does not match the site URL we
		 * were given, DNS resolution does not work or we have an SSL issue. We are going to determine which one is it.
		 */
		if (empty($data))
		{
			$uri      = new Uri($url);
			$hostname = $uri->getHost();
			$results  = dns_get_record($hostname, DNS_A);

			// If there are no IPv4 records let's try to get IPv6 records
			if (count($results) == 0)
			{
				$results = dns_get_record($hostname, DNS_AAAA);
			}

			// No DNS records. So, that's why fetching data failed!
			if (count($results) == 0)
			{
				// Delete the temporary file
				$connector->delete($connector->getPath(basename($sourceFile)));

				// And now throw the error
				throw new TransferFatalError(Text::sprintf('COM_AKEEBA_TRANSFER_ERR_DNS', $hostname));
			}

			/**
			 * The DNS resolution worked. The next theory we have to test is that the SSL certificate is invalid or
			 * self-signed. The best way to do that without having to go through the OpenSSL extensions (which might not
			 * be installed or activated) is to do no SSL checking and retry the download. If that works we definitely
			 * have an SSL issue.
			 */
			$contextOptions = $this->getProxyStreamContext();
			$contextOptions = array_merge_recursive($contextOptions, [
				'http' => [
					'timeout'         => 10,
					'follow_location' => 1,
				],
				'ssl'  => [
					'verify_peer'      => false,
					'verify_peer_name' => false,
				],
			]);
			$context        = stream_context_create($contextOptions);

			$data = @file_get_contents($url . '/' . basename($sourceFile), false, $context) ?: null;
		}

		// Delete the temporary file
		$connector->delete($connector->getPath(basename($sourceFile)));

		// Could we get it over HTTP?
		$originalData = file_get_contents($sourceFile);

		// Downloaded data is verified but the SSL certificate was bad: tell the user to fix the SSL certificate.
		if ($wrongSSL && ($originalData == $data))
		{
			throw new TransferFatalError(Text::_('COM_AKEEBA_TRANSFER_ERR_WRONGSSL'));
		}

		if ($originalData != $data)
		{
			throw new TransferFatalError(Text::_('COM_AKEEBA_TRANSFER_ERR_CANNOTACCESSTESTFILE'));
		}
	}

	/**
	 * Gets the FTP configuration from the session
	 *
	 * @return  array
	 */
	public function getFtpConfig()
	{
		$session = $this->container->segment;
		$transferOption = $session->get('transfer.transferOption', '');

		return array(
			'method'      => $transferOption,
			'force'       => $session->get('transfer.force', 0),
			'host'        => $session->get('transfer.ftpHost', ''),
			'port'        => $session->get('transfer.ftpPort', ''),
			'username'    => $session->get('transfer.ftpUsername', ''),
			'password'    => $session->get('transfer.ftpPassword', ''),
			'directory'   => $session->get('transfer.ftpDirectory', ''),
			'ssl'         => $transferOption == 'ftps',
			'passive'     => $session->get('transfer.ftpPassive', 1),
			'passive_fix' => $session->get('transfer.ftpPassiveFix', 1),
			'privateKey'  => $session->get('transfer.ftpPrivateKey', ''),
			'publicKey'   => $session->get('transfer.ftpPubKey', ''),
			'chunkMode'   => $session->get('transfer.chunkMode', 'chunked'),
			'chunkSize'   => $session->get('transfer.chunkSize', '5242880'),
		);
	}

	/**
	 * Removes files stored remotely
	 *
	 * @param   Transfer\TransferInterface  $connector         The transfer object
	 * @param   array                       $files             The list of remote files to delete (relative paths)
	 * @param   bool|true                   $ignoreExceptions  Should I ignore exceptions thrown?
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 */
	private function removeRemoteFiles(Transfer\TransferInterface $connector, array $files, $ignoreExceptions = true)
	{
		if (empty($files))
		{
			return;
		}

		foreach ($files as $file)
		{
			$remoteFile = $connector->getPath($file);

			try
			{
				$connector->delete($remoteFile);
			}
			catch (Exception $e)
			{
				// Only let the exception bubble up if we are told not to ignore exceptions
				if (!$ignoreExceptions)
				{
					throw $e;
				}
			}
		}
	}

	/**
	 * Check if the remote server environment matches our expectations.
	 *
	 * @param   bool    $forced     Are we forcing the transfer? If so some checks are ignored
	 *
	 * @throws  Exception
	 */
	private function checkRemoteServerEnvironment($forced)
	{
		$session = $this->container->segment;
		$baseUrl = $session->get('transfer.url', '');

		$baseUrl = rtrim($baseUrl, '/');

		$rawData = $this->httpGet($baseUrl . '/kickstart.php?task=serverinfo', [], 10);

		if ($rawData == false)
		{
			// Cannot access Kickstart on the remote server
			throw new RuntimeException(Text::_('COM_AKEEBA_TRANSFER_ERR_CANNOTRUNKICKSTART'));
		}

		// Try to get the raw JSON data
		$pos = strpos($rawData, '###');

		if ($pos === false)
		{
			// Invalid AJAX data, no leading ###
			throw new RuntimeException(Text::_('COM_AKEEBA_TRANSFER_ERR_CANNOTRUNKICKSTART'));
		}

		// Remove the leading ###
		$rawData = substr($rawData, $pos + 3);

		$pos = strpos($rawData, '###');

		if ($pos === false)
		{
			// Invalid AJAX data, no trailing ###
			throw new RuntimeException(Text::_('COM_AKEEBA_TRANSFER_ERR_CANNOTRUNKICKSTART'));
		}

		// Remove the trailing ###
		$rawData = substr($rawData, 0, $pos);

		// Get the JSON response
		$data = @json_decode($rawData, true);

		if (empty($data))
		{
			// Invalid AJAX data, can't decode this stuff
			throw new RuntimeException(Text::_('COM_AKEEBA_TRANSFER_ERR_CANNOTRUNKICKSTART'));
		}

		// Disk space check could be ignored since some hosts return the wrong value for the available disk space
		if (!$forced)
		{
			// Does the server have enough disk space?
			$freeSpace = $data['freeSpace'];

			$requiredSize = $this->getApproximateSpaceRequired();

			if ($requiredSize['size'] > $freeSpace)
			{
				$unit            = array('b', 'KB', 'MB', 'GB', 'TB', 'PB');
				$freeSpaceString = @round($freeSpace / pow(1024, ($i = floor(log($freeSpace, 1024)))), 2) . ' ' . $unit[$i];

				throw new TransferIgnorableError(Text::sprintf('COM_AKEEBA_TRANSFER_ERR_NOTENOUGHSPACE', $requiredSize['string'], $freeSpaceString));
			}
		}

		// Can I write to remote files?
		$canWrite = $data['canWrite'];
		$canWriteTemp = $data['canWriteTemp'];

		if (!$canWrite && !$canWriteTemp)
		{
			throw new RuntimeException(Text::_('COM_AKEEBA_TRANSFER_ERR_CANNOTWRITEREMOTEFILES'));
		}

		if ($canWrite)
		{
			$session->set('transfer.targetPath', '');
		}
		else
		{
			$session->set('transfer.targetPath', 'kicktemp');
		}

		$session->set('transfer.remoteTimeLimit', $data['maxExecTime']);

		// What is my upload limit?
		$uploadLimit = min($data['maxPost'], $data['maxUpload']);

		if (empty($data['maxPost']))
		{
			$uploadLimit = $data['maxUpload'];
		}
		elseif (empty($data['maxUpload']))
		{
			$uploadLimit = $data['maxPost'];
		}

		if (empty($uploadLimit))
		{
			$uploadLimit = 1048576;
		}

		$session->set('transfer.uploadLimit', $uploadLimit, 'akeeba');
	}

	/**
	 * Get the filename for a backup part file, given the base file and the part number
	 *
	 * @param   string  $baseFile  Full path to the base file (.jpa, .jps, .zip)
	 * @param   int     $part      Part number
	 *
	 * @return  string
	 */
	private function getPartFilename($baseFile, $part = 0)
	{
		if ($part == 0)
		{
			return $baseFile;
		}

		$dirname = dirname($baseFile);
		$basename = basename($baseFile);

		$pos = strrpos($basename, '.');
		$extension = substr($basename, $pos + 1);

		$newExtension = substr($baseFile, 0, 1) . sprintf('%02u', $part);

		return $dirname . '/' . basename($basename, '.' . $extension) . '.'  .$newExtension;
	}

	/**
	 * Returns the PHP memory limit. If ini_get is not available it will assume 8Mb.
	 *
	 * @return  int
	 */
	private function getServerMemoryLimit()
	{
		// Default reported memory limit: 8Mb
		$memLimit = 8388608;

		// If we can't find out how much PHP memory we have available use 8Mb by default
		if (!function_exists('ini_get'))
		{
			return $memLimit;
		}

		$iniMemLimit = ini_get("memory_limit");
		$iniMemLimit = $this->convertMemoryLimitToBytes($iniMemLimit);

		$memLimit = ($iniMemLimit > 0) ? $iniMemLimit : $memLimit;

		return (int) $memLimit;
	}

	/**
	 * Gets the maximum chunk size the server can handle safely. It does so by finding the PHP memory limit, removing
	 * the current memory usage (or at least 2Mb) and rounding down to the closest 512Kb. It can never be lower than
	 * 512Kb.
	 */
	private function getMaxChunkSize()
	{
		$memoryLimit = $this->getServerMemoryLimit();
		$usedMemory = max(memory_get_usage(), memory_get_peak_usage(), 2048);

		$maxChunkSize = max(($memoryLimit - $usedMemory) / 2, 524288);

		return floor($maxChunkSize / 524288) * 524288;
	}

	/**
	 * Convert the textual representation of PHP memory limit to an integer, e.g. convert 8M to 8388608
	 *
	 * @param   string  $setting  The PHP memory limit
	 *
	 * @return  int  PHP memory limit as an integer
	 */
	private function convertMemoryLimitToBytes($setting)
	{
		$val  = trim($setting);
		$last = strtolower(substr($val, -1));

		if (is_numeric($last))
		{
			return $setting;
		}

		$val = substr($val, 0, -1);

		switch ($last)
		{
			case 't':
				$val *= 1024;
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return (int) $val;
	}

	/**
	 * Uploads a chunk of a backup part file using a direct POST to Kickstart.
	 *
	 * This is the method supported by the Site Transfer Wizard since its inception. However, it may not work with hosts
	 * which have a sensitive server protection, e.g. the very tight mod_security2 rules on SiteGround servers. In those
	 * cases the remote server will respond with a 500 Internal Server Error, a 403 Forbidden or another server error.
	 *
	 * @param   string   $fileName     The filename to upload
	 * @param   string   $data         The data to upload
	 * @param   Segment  $session      The current session fragment
	 *
	 * @return  int      The length of the data we managed to upload
	 *
	 * @since   3.1.0
	 */
	private function uploadUsingPost($fileName, $data, $session)
	{
		$frag      = $session->get('transfer.frag', -1);
		$fragSize  = $session->get('transfer.fragSize', 5242880);
		$url       = $session->get('transfer.url', '');
		$directory = $session->get('transfer.targetPath', '');

		$url = rtrim($url, '/') . '/kickstart.php';
		$uri = Uri::getInstance($url);
		$uri->setVar('task', 'uploadFile');
		$uri->setVar('file', basename($fileName));
		$uri->setVar('directory', $directory);
		$uri->setVar('frag', $frag);
		$uri->setVar('fragSize', $fragSize);

		$phpTimeout = 10;

		if (function_exists('ini_get'))
		{
			$phpTimeout = (int) ini_get('max_execution_time') ?: 3600;
			$phpTimeout = min($phpTimeout, 3600);
		}

		$dataLength = function_exists('mb_strlen') ? mb_strlen($data, 'ASCII') : strlen($data);

		$rawData = $this->httpPost($uri->toString(), http_build_query([
			'data' => $data
		]), [], $phpTimeout);

		unset($data);

		// Try to get the raw JSON data
		$pos = strpos($rawData, '###');

		if ($pos === false)
		{
			// Invalid AJAX data, no leading ###
			throw new RuntimeException(Text::sprintf('COM_AKEEBA_TRANSFER_ERR_CANNOTUPLOADARCHIVE', basename($fileName)));
		}

		// Remove the leading ###
		$rawData = substr($rawData, $pos + 3);

		$pos = strpos($rawData, '###');

		if ($pos === false)
		{
			// Invalid AJAX data, no trailing ###
			throw new RuntimeException(Text::sprintf('COM_AKEEBA_TRANSFER_ERR_CANNOTUPLOADARCHIVE', basename($fileName)));
		}

		// Remove the trailing ###
		$rawData = substr($rawData, 0, $pos);

		// Get the JSON response
		$data = @json_decode($rawData, true);

		if (empty($data))
		{
			// Invalid AJAX data, can't decode this stuff
			throw new RuntimeException(Text::sprintf('COM_AKEEBA_TRANSFER_ERR_CANNOTUPLOADARCHIVE', basename($fileName)));
		}

		if (!$data['status'])
		{
			throw new RuntimeException(Text::sprintf('COM_AKEEBA_TRANSFER_ERR_ERRORFROMREMOTE', $data['message']));
		}

		return $dataLength;
	}

	/**
	 * Uploads a chunk of a backup part file via FTP and then uses Kickstart to piece the file together.
	 *
	 * This is a new upload method which works better on servers with tighter security. The only downside is that we
	 * have to open many FTP/SFTP upload sessions which may result in the remote server eventually blocking our uploads.
	 *
	 * @param   string   $fileName     The filename to upload
	 * @param   string   $data         The data to upload
	 * @param   Segment  $session      The current session fragment
	 * @param   array    $config       The FTP/SFTP configuration
	 *
	 * @return  int      The length of the data we managed to upload
	 *
	 * @since   3.1.0
	 */
	private function uploadUsingChunked($fileName, $data, $session, $config)
	{
		// ==== Initialize
		$frag      = $session->get('transfer.frag', -1);
		$fragSize  = $session->get('transfer.fragSize', 5242880);
		$url       = $session->get('transfer.url', '');
		$directory = $session->get('transfer.targetPath', '');

		// ==== Upload the data to the same folder as Kickstart, under a temporary name
		// Even though the connector has the write() method, it's not very good for over 1M files. So we create a temp file instead.
		$engineConfig = Factory::getConfiguration();
		$localTempFile = tempnam($this->container->temporaryPath, 'stw');
		$localTempFile = ($localTempFile === false) ? tempnam(sys_get_temp_dir(), 'stw') : $localTempFile;
		$localTempFile = ($localTempFile === false) ? tempnam($engineConfig->get('akeeba.basic.output_directory', '[DEFAULT_OUTPUT]'), 'stw') : $localTempFile;

		if ($localTempFile === false)
		{
			throw new RuntimeException(Text::_('COM_AKEEBA_TRANSFER_ERR_CANTCREATETEMPCHUNK'));
		}

		if (!$this->container->fileSystem->write($localTempFile, $data))
		{
			throw new RuntimeException(Text::_('COM_AKEEBA_TRANSFER_ERR_CANTCREATETEMPCHUNK'));
		}

		$random    = new RandomValue();
		$tempFile  = strtolower($random->generateString(8)) . '.dat';
		$connector = $this->getConnector($config);

		try
		{
			$remoteDirectory = $config['directory'] . (empty($directory) ? '' : ('/' . $directory));
			$remoteFile      = $remoteDirectory . '/' . $tempFile;

			$uploaded = $connector->upload($localTempFile, $remoteFile, true);
		}
		catch (RuntimeException $e)
		{
			$this->container->fileSystem->delete($localTempFile);

			throw $e;
		}

		if (!$uploaded)
		{
			throw new RuntimeException(Text::sprintf('COM_AKEEBA_TRANSFER_ERR_CANNOTUPLOADTEMP', $localTempFile, $remoteFile));
		}

		// ==== Call Kickstart to piece together the file
		$url = rtrim($url, '/') . '/kickstart.php';
		$uri = Uri::getInstance($url);
		$uri->setVar('task', 'uploadFile');
		$uri->setVar('file', basename($fileName));
		$uri->setVar('directory', $directory);
		$uri->setVar('frag', $frag);
		$uri->setVar('fragSize', $fragSize);
		$uri->setVar('dataFile', $tempFile);

		$phpTimeout = 10;

		if (function_exists('ini_get'))
		{
			$phpTimeout = (int) ini_get('max_execution_time') ?: 3600;
			$phpTimeout = min($phpTimeout, 3600);
		}

		$dataLength = function_exists('mb_strlen') ? mb_strlen($data, 'ASCII') : strlen($data);

		$rawData = $this->httpGet($uri->toString(), [], $phpTimeout);

		// ==== Delete the temporary files
		$this->container->fileSystem->delete($localTempFile);
		$connector->delete($remoteFile);

		// ==== Parse Kickstart's response

		// Try to get the raw JSON data
		$pos = strpos($rawData, '###');

		if ($pos === false)
		{
			// Invalid AJAX data, no leading ###
			throw new RuntimeException(Text::sprintf('COM_AKEEBA_TRANSFER_ERR_CANNOTUPLOADARCHIVE', basename($fileName)));
		}

		// Remove the leading ###
		$rawData = substr($rawData, $pos + 3);

		$pos = strpos($rawData, '###');

		if ($pos === false)
		{
			// Invalid AJAX data, no trailing ###
			throw new RuntimeException(Text::sprintf('COM_AKEEBA_TRANSFER_ERR_CANNOTUPLOADARCHIVE', basename($fileName)));
		}

		// Remove the trailing ###
		$rawData = substr($rawData, 0, $pos);

		// Get the JSON response
		$data = @json_decode($rawData, true);

		if (empty($data))
		{
			// Invalid AJAX data, can't decode this stuff
			throw new RuntimeException(Text::sprintf('COM_AKEEBA_TRANSFER_ERR_CANNOTUPLOADARCHIVE', basename($fileName)));
		}

		if (!$data['status'])
		{
			throw new RuntimeException(Text::sprintf('COM_AKEEBA_TRANSFER_ERR_ERRORFROMREMOTE', $data['message']));
		}

		return $dataLength;
	}


	/**
	 * Perform an HTTP GET and return the results.
	 *
	 * This method is rigged to work EVEN IF the TLS/SSL certificate of the target server is invalid or self-signed.
	 * This is unfortunately a very typical use case when transferring sites as the target site most often than not is
	 * not fully set up yet (no domain assigned, no TLS certificate assigned and so on).
	 *
	 * If, however, the domain name of the target URL cannot resolve neither as IPv4 nor as IPv6 we'll throw an
	 * exception.
	 *
	 * @param   string  $url      The URL to fetch
	 * @param   array   $headers  Any headers to send (optional). Default: none.
	 * @param   int     $timeout  The timeout in seconds (optional). Default: 10 seconds.
	 *
	 * @return  string|null
	 * @since   8.1.4
	 */
	private function httpGet(string $url, array $headers = [], int $timeout = 10): ?string
	{
		// First I'm going to try with the HTTP factory which is the most reliable method for properly set up sites.
		$options     = [];
		$proxyParams = Platform::getInstance()->getProxySettings();

		if ($proxyParams['enabled'])
		{
			$options['proxy'] = [
				'host' => $proxyParams['host'],
				'port' => $proxyParams['port'],
				'user' => $proxyParams['user'],
				'pass' => $proxyParams['pass'],
			];
		}

		$download = new Download($this->container);

		// Headers are provided as a dictionary. PHP expects them as a plain array of "Header-Name: Value" entries.
		if (isset($headers))
		{
			$headers = array_map(function ($k, $v) {
				if (is_numeric($k) && strpos($v, ':') !== false)
				{
					return $v;
				}

				return $k . ':' . $v;
			}, array_keys($headers), array_values($headers));
		}

		if ($download->getAdapterName() === 'fopen')
		{
			$options['http']['timeout'] = $timeout;

			if (!empty($headers))
			{
				$options['http']['header'] = array_values($headers);
			}
		}
		else
		{
			$options[CURLOPT_TIMEOUT]    = $timeout;
			$options[CURLOPT_HTTPHEADER] = array_values($headers);
		}

		$download->setAdapterOptions($options);

		try
		{
			$data = $download->getFromURL($url);
		}
		catch (Exception $e)
		{
			// We absorb all exceptions since they are all generic, it's not a different exception per error type :(
			$data = false;
		}

		// Non-null returns mean that the HTTP factory worked. Return early and spare us the trouble.
		if ($data !== false)
		{
			return $data;
		}

		// Does the domain name resolve?
		$uri      = new Uri($url);
		$hostname = strtolower($uri->getHost());

		if (!isset(self::$domainResolvable[$hostname]))
		{
			$results = dns_get_record($hostname, DNS_A);

			// If there are no IPv4 records let's try to get IPv6 records
			if (((is_array($results) || ($results instanceof Countable)) ? count($results) : 0) == 0)
			{
				$results = dns_get_record($hostname, DNS_AAAA);
			}

			// No DNS records. So, that's why fetching data failed!
			self::$domainResolvable[$hostname] = (is_array($results) || $results instanceof Countable ? count($results) : 0) > 0;
		}

		// If the domain doesn't resolve complain loudly.
		if (!self::$domainResolvable[$hostname])
		{
			throw new TransferFatalError(Text::sprintf('COM_AKEEBA_TRANSFER_ERR_DNS', $hostname));
		}

		/**
		 * The DNS resolution worked. A different error has occurred. Unfortunately, we don't know WHAT happened so we
		 * will make an assumption that the problem is that the TLS/SSL certificate is invalid (e.g. wrong Common Name)
		 * or self-signed. We are going to use the PHP URL fopen wrappers to try and run the request regardless. This is
		 * not very secure but, as we said, it's an unfortunate reality of how this feature is used :(
		 */
		$contextOptions = $this->getProxyStreamContext();
		$contextOptions = array_merge_recursive($contextOptions, [
			'http' => [
				'timeout'         => $timeout,
				'follow_location' => 1,
			],
			'ssl'  => [
				'verify_peer'      => false,
				'verify_peer_name' => false,
			],
		]);

		if (!empty($headers))
		{
			$context['http']['header'] = array_values($headers);
		}

		// Create the context and run the request
		$context = stream_context_create($contextOptions);

		return @file_get_contents($url, false, $context) ?: null;
	}

	/**
	 * Perform an HTTP POST and return the results.
	 *
	 * This method is rigged to work EVEN IF the TLS/SSL certificate of the target server is invalid or self-signed.
	 * This is unfortunately a very typical use case when transferring sites as the target site most often than not is
	 * not fully set up yet (no domain assigned, no TLS certificate assigned and so on).
	 *
	 * If, however, the domain name of the target URL cannot resolve neither as IPv4 nor as IPv6 we'll throw an
	 * exception.
	 *
	 * @param   string  $url      The URL to fetch
	 * @param   string  $data     The data to send over POST
	 * @param   array   $headers  Any headers to send (optional). Default: none.
	 * @param   int     $timeout  The timeout in seconds (optional). Default: 10 seconds.
	 *
	 * @return  string|null
	 * @since   7.6.4
	 */
	private function httpPost(string $url, string $data, array $headers = [], int $timeout = 10): ?string
	{
		// Add necessary headers
		if (!isset($headers['Content-Type']))
		{
			$headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=utf-8';
		}

		$headers['Content-Length'] = function_exists('mb_strlen') ? \mb_strlen($data, 'ASCII') : \strlen($data);

		// First I'm going to try with the HTTP factory which is the most reliable method for properly set up sites.
		$options     = [];
		$proxyParams = Platform::getInstance()->getProxySettings();

		if ($proxyParams['enabled'])
		{
			$options['proxy'] = [
				'host' => $proxyParams['host'],
				'port' => $proxyParams['port'],
				'user' => $proxyParams['user'],
				'pass' => $proxyParams['pass'],
			];
		}

		$download = new Download($this->container);

		// Headers are provided as a dictionary. PHP expects them as a plain array of "Header-Name: Value" entries.
		if (isset($headers))
		{
			$headers = array_map(function ($k, $v) {
				if (is_numeric($k) && strpos($v, ':') !== false)
				{
					return $v;
				}

				return $k . ':' . $v;
			}, array_keys($headers), array_values($headers));
		}

		if ($download->getAdapterName() === 'fopen')
		{
			$options['http']['timeout'] = $timeout;

			if (!empty($headers))
			{
				$options['http']['header'] = array_values($headers);
			}

			$options['http']['method']  = 'POST';
			$options['http']['content'] = $data;
		}
		else
		{
			$options[CURLOPT_TIMEOUT]       = $timeout;
			$options[CURLOPT_HTTPHEADER]    = array_values($headers);
			$options[CURLOPT_CUSTOMREQUEST] = 'POST';
			$options[CURLOPT_POSTFIELDS]    = [
				'data' => $data,
			];
		}

		$download->setAdapterOptions($options);

		try
		{
			$ret = $download->getFromURL($url);
		}
		catch (Exception $e)
		{
			$ret = false;
		}

		// Non-null returns mean that the HTTP factory worked. Return early and spare us the trouble.
		if ($ret !== false)
		{
			return $ret;
		}

		// Does the domain name resolve?
		$uri      = new Uri($url);
		$hostname = strtolower($uri->getHost());

		if (!isset(self::$domainResolvable[$hostname]))
		{
			$results = dns_get_record($hostname, DNS_A);

			// If there are no IPv4 records let's try to get IPv6 records
			if (((is_array($results) || ($results instanceof Countable)) ? count($results) : 0) == 0)
			{
				$results = dns_get_record($hostname, DNS_AAAA);
			}

			// No DNS records. So, that's why fetching data failed!
			self::$domainResolvable[$hostname] = (is_array($results) || $results instanceof Countable ? count($results) : 0) > 0;
		}

		// If the domain doesn't resolve complain loudly.
		if (!self::$domainResolvable[$hostname])
		{
			throw new TransferFatalError(Text::sprintf('COM_AKEEBA_TRANSFER_ERR_DNS', $hostname));
		}

		/**
		 * The DNS resolution worked. A different error has occurred. Unfortunately, we don't know WHAT happened so we
		 * will make an assumption that the problem is that the TLS/SSL certificate is invalid (e.g. wrong Common Name)
		 * or self-signed. We are going to use the PHP URL fopen wrappers to try and run the request regardless. This is
		 * not very secure but, as we said, it's an unfortunate reality of how this feature is used :(
		 */

		$contextOptions = $this->getProxyStreamContext();
		$contextOptions = array_merge_recursive($contextOptions, [
			'http' => [
				'method'          => 'POST',
				'content'         => $data,
				'timeout'         => $timeout,
				'follow_location' => 1,
				'header'          => array_values($headers),
			],
			'ssl'  => [
				'verify_peer'      => false,
				'verify_peer_name' => false,
			],
		]);

		// Create the context and run the request
		$context = stream_context_create($contextOptions);

		return @file_get_contents($url, false, $context) ?: null;
	}

	protected function getProxyStreamContext()
	{
		$ret           = [];
		$proxySettings = Platform::getInstance()->getProxySettings();

		if (!$proxySettings['enabled'])
		{
			return $ret;
		}

		$ret['http'] = [
			'proxy'           => $proxySettings['host'] . ':' . $proxySettings['port'],
			'request_fulluri' => true,
		];
		$ret['ftp']  = [
			'proxy'           => $proxySettings['host'] . ':' . $proxySettings['port'],
			// So, request_fulluri isn't documented for the FTP transport but seems to be required...?!
			'request_fulluri' => true,
		];

		if (empty($proxySettings['user']))
		{
			return $ret;
		}

		$ret['http']['header'] = ['Proxy-Authorization: Basic ' . base64_encode($proxySettings['user'] . ':' . $proxySettings['pass'])];
		$ret['ftp']['header'] = ['Proxy-Authorization: Basic ' . base64_encode($proxySettings['user'] . ':' . $proxySettings['pass'])];

		return $ret;
	}
}
