<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('ADMINTOOLSINC') or die;

use Akeeba\AdminTools\Admin\Helper\HtaccessManager;
use Akeeba\AdminTools\Admin\Helper\Wordpress;
use DateTime;
use DateTimeZone;
use Exception;

class HtaccessMaker extends ServerConfigMaker
{
	/**
	 * The current configuration of this feature
	 *
	 * @var  object
	 */
	protected $config = null;

	/**
	 * The default configuration of this feature.
	 *
	 * Note that you define an array. It becomes an object in the constructor. We have to do that since PHP doesn't
	 * allow the initialization of anonymous objects (like e.g. Javascript) but lets us typecast an array to an object
	 * – just not in the property declaration!
	 *
	 * @var  object
	 */
	public $defaultConfig = [
		// == System configuration ==
		// Host name for HTTPS requests (without https://)
		'httpshost'           => '',
		// Host name for HTTP requests (without http://)
		'httphost'            => '',
		// Follow symlinks (may cause a blank page or 500 Internal Server Error)
		'symlinks'            => 0,
		// Base directory of your site (/ for domain's root)
		'rewritebase'         => '',

		// == Optimization and utility ==
		// Force index.php parsing before index.html
		'fileorder'           => 1,
		// Set default expiration time to 1 hour
		'exptime'             => 1,
		// Automatically compress static resources
		'autocompress'        => 1,
		// Force GZip compression for mangled Accept-Encoding headers
		'forcegzip'           => 1,
		// Redirect index.php to root
		'autoroot'            => 1,
		// Redirect www and non-www addresses
		'wwwredir'            => 0,
		// Redirect old to new domain
		'olddomain'           => '',
		// Force HTTPS for these URLs
		'httpsurls'           => [],
		// HSTS Header (for HTTPS-only sites)
		'hstsheader'          => 0,
		// Disable HTTP methods TRACE and TRACK (protect against XST)
		'notracetrack'        => 0,
		// Cross-Origin Resource Sharing (CORS)
		'cors'                => 0,
		// Set UTF-8 charset as default
		'utf8charset'         => 0,
		// Send ETag
		'etagtype'            => 'default',
		// Referrer policy
		'referrerpolicy'	  => 'unsafe-url',

		// == Basic security ==
		// Disable directory listings
		'nodirlists'          => 0,
		// Protect against common file injection attacks
		'fileinj'             => 1,
		// Disable PHP Easter Eggs
		'phpeaster'           => 1,
		// Block access from specific user agents
		'nohoggers'           => 0,
		// Block access to configuration.php-dist and htaccess.txt
		'leftovers'           => 1,
		// Protect against clickjacking
		'clickjacking'        => 0,
		// Reduce MIME type security risks
		'reducemimetyperisks' => 0,
		// Reflected XSS prevention
		'reflectedxss'        => 0,
		// Remove Apache and PHP version signature
		'noserversignature'   => 1,
		// Prevent content transformation
		'notransform'         => 0,
		// User agents to block (one per line)
		'hoggeragents'        => [
			'WebBandit',
			'webbandit',
			'Acunetix',
			'binlar',
			'BlackWidow',
			'Bolt 0',
			'Bot mailto:craftbot@yahoo.com',
			'BOT for JCE',
			'casper',
			'checkprivacy',
			'ChinaClaw',
			'clshttp',
			'cmsworldmap',
			'comodo',
			'Custo',
			'Default Browser 0',
			'diavol',
			'DIIbot',
			'DISCo',
			'dotbot',
			'Download Demon',
			'eCatch',
			'EirGrabber',
			'EmailCollector',
			'EmailSiphon',
			'EmailWolf',
			'Express WebPictures',
			'extract',
			'ExtractorPro',
			'EyeNetIE',
			'feedfinder',
			'FHscan',
			'FlashGet',
			'flicky',
			'GetRight',
			'GetWeb!',
			'Go-Ahead-Got-It',
			'Go!Zilla',
			'grab',
			'GrabNet',
			'Grafula',
			'harvest',
			'HMView',
			'ia_archiver',
			'Image Stripper',
			'Image Sucker',
			'InterGET',
			'Internet Ninja',
			'InternetSeer.com',
			'jakarta',
			'Java',
			'JetCar',
			'JOC Web Spider',
			'kmccrew',
			'larbin',
			'LeechFTP',
			'libwww',
			'Mass Downloader',
			'Maxthon$',
			'microsoft.url',
			'MIDown tool',
			'miner',
			'Mister PiX',
			'NEWT',
			'MSFrontPage',
			'Navroad',
			'NearSite',
			'Net Vampire',
			'NetAnts',
			'NetSpider',
			'NetZIP',
			'nutch',
			'Octopus',
			'Offline Explorer',
			'Offline Navigator',
			'PageGrabber',
			'Papa Foto',
			'pavuk',
			'pcBrowser',
			'PeoplePal',
			'planetwork',
			'psbot',
			'purebot',
			'pycurl',
			'RealDownload',
			'ReGet',
			'Rippers 0',
			'SeaMonkey$',
			'sitecheck.internetseer.com',
			'SiteSnagger',
			'skygrid',
			'SmartDownload',
			'sucker',
			'SuperBot',
			'SuperHTTP',
			'Surfbot',
			'tAkeOut',
			'Teleport Pro',
			'Toata dragostea mea pentru diavola',
			'turnit',
			'vikspider',
			'VoidEYE',
			'Web Image Collector',
			'Web Sucker',
			'WebAuto',
			'WebCopier',
			'WebFetch',
			'WebGo IS',
			'WebLeacher',
			'WebReaper',
			'WebSauger',
			'Website eXtractor',
			'Website Quester',
			'WebStripper',
			'WebWhacker',
			'WebZIP',
			'Wget',
			'Widow',
			'WWW-Mechanize',
			'WWWOFFLE',
			'Xaldon WebSpider',
			'Yandex',
			'Zeus',
			'zmeu',
			'CazoodleBot',
			'discobot',
			'ecxi',
			'GT::WWW',
			'heritrix',
			'HTTP::Lite',
			'HTTrack',
			'ia_archiver',
			'id-search',
			'id-search.org',
			'IDBot',
			'Indy Library',
			'IRLbot',
			'ISC Systems iRc Search 2.1',
			'LinksManager.com_bot',
			'linkwalker',
			'lwp-trivial',
			'MFC_Tear_Sample',
			'Microsoft URL Control',
			'Missigua Locator',
			'panscient.com',
			'PECL::HTTP',
			'PHPCrawl',
			'PleaseCrawl',
			'SBIder',
			'Snoopy',
			'Steeler',
			'URI::Fetch',
			'urllib',
			'Web Sucker',
			'webalta',
			'WebCollage',
			'Wells Search II',
			'WEP Search',
			'zermelo',
			'ZyBorg',
			'Indy Library',
			'libwww-perl',
			'Go!Zilla',
			'TurnitinBot',
			'sqlmap',
		],

		// == Server protection ==
		// -- Toggle protection
		'siteprot'         => 0,
		// -- Fine-tuning
		// File types allowed
		'extypes'          => [
			'7z', 'appcache', 'atom', 'avi', 'bbaw', 'bmp', 'crx', 'css', 'cur', 'doc', 'docx', 'eot', 'f4a', 'f4b', 'f4p', 'f4v', 'flv', 'geojson', 'gif', 'htc', 'htm', 'html', 'ico', 'jpeg', 'jpe', 'jpg', 'jp2', 'jpe2', 'js', 'json', 'jsonl', 'jsond', 'm4a', 'm4v', 'manifest', 'map', 'mkv', 'mp3', 'mp4', 'mpg', 'mpeg', 'ods', 'odp', 'odt', 'oex', 'oga', 'ogg', 'ogv', 'opus', 'otf', 'png', 'pdf', 'png', 'ppt', 'pptx', 'rar', 'rdf', 'rss', 'safariextz', 'svg', 'svgz', 'swf', 'tar', 'topojson', 'tbz', 'tbz2', 'tgz', 'ttc', 'ttf', 'txt', 'txz', 'vcard', 'vcf', 'vtt', 'wav', 'webapp', 'webm', 'webp', 'woff', 'woff2', 'xloc', 'xls', 'xlsx', 'xml', 'xpi', 'xps', 'xz', 'zip', 'xsl',
			'7Z', 'APPCACHE', 'ATOM', 'AVI', 'BBAW', 'BMP', 'CRX', 'CSS', 'CUR', 'DOC', 'DOCX', 'EOT', 'F4A', 'F4B', 'F4P', 'F4V', 'FLV', 'GEOJSON', 'GIF', 'HTC', 'HTM', 'HTML', 'ICO', 'JPEG', 'JPE', 'JPG', 'JP2', 'JPE2', 'JS', 'JSON', 'JSONL', 'JSOND', 'M4A', 'M4V', 'MANIFEST', 'MAP', 'MKV', 'MP3', 'MP4', 'MPG', 'MPEG', 'ODS', 'ODP', 'ODT', 'OEX', 'OGA', 'OGG', 'OGV', 'OPUS', 'OTF', 'PNG', 'PDF', 'PNG', 'PPT', 'PPTX', 'RAR', 'RDF', 'RSS', 'SAFARIEXTZ', 'SVG', 'SVGZ', 'SWF', 'TAR', 'TOPOJSON', 'TBZ', 'TBZ2', 'TGZ', 'TTC', 'TTF', 'TXT', 'TXZ', 'VCARD', 'VCF', 'VTT', 'WAV', 'WEBAPP', 'WEBM', 'WEBP', 'WOFF', 'WOFF2', 'XLOC', 'XLS', 'XLSX', 'XML', 'XPI', 'XPS', 'XZ', 'ZIP', 'XSL',
		],
		// Where are the file types allowed
		'exdirs' => [
		],
		// -- Exceptions
		// Allow direct access to these files
		'exceptionfiles'      => [
			'wp-activate.php',
			'wp-comments-post.php',
			'wp-cron.php',
			'wp-links-opml.php',
			'wp-mail.php',
			'wp-signup.php',
			'wp-trackback.php',
			'xmlrpc.php',
			'wp-includes/js/tinymce/wp-tinymce.php',
			'wp-content/plugins/akeebabackupwp/app/index.php',
			'wp-content/plugins/akeebabackupwp/app/restore.php',
			'wp-content/plugins/akeebabackupwp/app/remote.php',
			'installation/index.php',
			'kickstart.php',
			'wp-content/plugins/wordpress-seo/css/main-sitemap.xsl',
		],
		// Allow direct access, except .php files, to these directories
		'exceptiondirs'       => [
			'.well-known',
			'installation',
		],
		// Allow direct access, including .php files, to these directories
		'fullaccessdirs'      => [
			'wp-content/upgrade'
		],

		// == Custom .htaccess rules ==
		// At the top of the file
		'custhead'            => '',
		// At the bottom of the file
		'custfoot'            => '',
		// Disable client-side risky behavior in static content
		'staticrisks'       => 1,
	];

	/**
	 * The current configuration of this feature
	 *
	 * @var  object
	 */
	protected $configKey = 'htconfig';

	/**
	 * The base name of the configuration file being saved by this feature, e.g. ".htaccess". The file is always saved
	 * in the site's root. Any old files under that name are renamed with a .admintools suffix.
	 *
	 * @var string
	 */
	protected $configFileName = '.htaccess';

	/**
	 * Nukes current server configuration file, removing all custom rules added by Admin Tools
	 */
	public function nuke()
	{
		try
		{
			$htaccessManager = HtaccessManager::getInstance();
		}
		catch (\RuntimeException $e)
		{
			// Something happened while reading the htaccess file. Stop here
			return;
		}

		$htaccessFile = get_home_path() . '.htaccess';

		// If for any reason we do not have the htaccess file stop here
		if (!file_exists($htaccessFile))
		{
			return;
		}

		$startMarker       = $htaccessManager->startingMark;
		$endMarker         = $htaccessManager->endingMark;
		$legacyStartMarker = $htaccessManager->legacyStartingMark;
		$legacyEndMarker   = $htaccessManager->legacyEndingMark;

		$contents = file_get_contents($htaccessFile);

		// Double check that our markers are there  (both new and legacy ones)
		if (
			(stripos($contents, $startMarker) === false)       || (stripos($contents, $endMarker) === false) &&
			(stripos($contents, $legacyStartMarker) === false) || (stripos($contents, $legacyEndMarker) === false)
		)
		{
			return;
		}

		// Ok, now let's read the contents and skip all data within our markers, leaving everything else intact
		$lines        = explode("\n", $contents);
		$inSection    = false;
		$new_contents = [];

		foreach ($lines as $line)
		{
			$line = trim($line);

			// If we get the starting marker, raise the flag we're in our section
			if ((stripos($line, $startMarker) !== false) || (stripos($line, $legacyStartMarker) !== false))
			{
				$inSection = true;
			}

			// If we get the ending marker, we should stop skipping lines
			if ((stripos($line, $endMarker) !== false) || (stripos($line, $legacyEndMarker) !== false))
			{
				$inSection = false;

				// Continue here or we'll add our marker into the new file
				continue;
			}

			if ($inSection)
			{
				continue;
			}

			$new_contents[] = $line;
		}

		$new_contents = implode("\n", $new_contents);

		file_put_contents($htaccessFile, $new_contents);
	}

	/**
	 * Compile and return the contents of the .htaccess configuration file
	 *
	 * @return  string
	 * @throws Exception
	 */
	public function makeConfigFile()
	{
		// Guess Apache features
		$apacheVersion = $this->apacheVersion();
		$serverCaps    = (object) [
			'customCodes' => version_compare($apacheVersion, '2.2', 'ge'), // Custom redirections, e.g. R=301
			'deflate'     => version_compare($apacheVersion, '2.0', 'ge') // mod_deflate support
		];

		$redirCode     = $serverCaps->customCodes ? '[R=301,L]' : '[R,L]';

		$date = new DateTime();
		$tz   = new DateTimeZone(Wordpress::get_timezone_string());
		$date->setTimezone($tz);

		$d        = $date->format('Y-m-d H:i:s T');
		$version  = ADMINTOOLSWP_VERSION;
		$banner   = <<<END
### ===========================================================================
### Security Enhanced & Highly Optimized .htaccess File for WordPress
### automatically generated by Admin Tools $version on $d
### Auto-detected Apache version: $apacheVersion (best guess)
### ===========================================================================
###
### Admin Tools is Free Software, distributed under the terms of the GNU
### General Public License version 3 or, at your option, any later version
### published by the Free Software Foundation.
###
### !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! IMPORTANT !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
### !!                                                                       !!
### !!  If you get an Internal Server Error 500 or a blank page when trying  !!
### !!  to access your site, remove this file and try tweaking its settings  !!
### !!  in the back-end of the Admin Tools component.                        !!
### !!                                                                       !!
### !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
###

END;

		$config = $this->loadConfiguration();

		// Is HSTS enabled?
		$hasHSTS = $config->hstsheader == 1;

		$htaccess = HtaccessManager::getInstance();
		$htaccess->setOption('Banner', $banner);

		$htaccess->setOption('RewriteEngine', 'RewriteEngine On');

		$rewritebase = $config->rewritebase;
		$htaccess->setOption('RewriteBase', null);

		if (!empty($rewritebase))
		{
			$rewritebase = trim($rewritebase, '/');
			$htaccess->setOption('RewriteBase', "RewriteBase /$rewritebase");
		}

		$htaccess->setOption('HTTPtoHTTPS', null);

		if ($hasHSTS)
		{
			$httpsHost = $config->httpshost;
			$HttpToHttps = [
				"## Since you have enabled HSTS the first redirection rule will instruct the browser to visit the HTTPS version of your",
				"## site. This prevents unsafe redirections through HTTP.",
				"RewriteCond %{HTTPS} !=on [OR]",
				"RewriteCond %{HTTP:X-Forwarded-Proto} =http",
				"RewriteRule .* https://$httpsHost%{REQUEST_URI} [L,R=301]"
			];

			$htaccess->setOption('HTTPtoHTTPS', $HttpToHttps);
		}

		$htaccess->setOption('CustHead', null);

		if (!empty($config->custhead))
		{
			$rules = [
				"##### Custom Rules (Top of File)",
				$config->custhead
			];

			$htaccess->setOption('CustHead', $rules);
		}

		$htaccess->setOption('FileOrder', null);

		if ($config->fileorder == 1)
		{
			$rules = [
				"##### File execution order",
				"DirectoryIndex index.php index.html"
			];

			$htaccess->setOption('FileOrder', $rules);
		}

		$htaccess->setOption('NoDirLists', null);
		$htaccess->setOption('Symlinks', null);

		if ($config->nodirlists == 1)
		{
			$rules   = [];
			$rules[] = "##### No directory listings";
			$rules[] = "IndexIgnore *";

			switch ($config->symlinks)
			{
				case 0:
					$rules[] = "Options -Indexes";
					break;

				case 1:
					$rules[] = "Options -Indexes +FollowSymLinks";
					break;

				case 2:
					$rules[] = "Options -Indexes +SymLinksIfOwnerMatch";
					break;
			}

			$htaccess->setOption('NoDirLists', $rules);
		}
		elseif ($config->symlinks != 0)
		{
			$rules   = [];
			$rules[] = "##### Follow symlinks";

			switch ($config->symlinks)
			{
				case 1:
					$rules[] = "Options +FollowSymLinks";
					break;

				case 2:
					$rules[] = "Options +SymLinksIfOwnerMatch";
					break;
			}

			$htaccess->setOption('Symlinks', $rules);
		}

		$htaccess->setOption('ExpTime', null);

		if ($config->exptime != 0)
		{
			$expWeek = '1 week';
			$expMonth = '1 month';

			if ($config->exptime == 2)
			{
				$expWeek = '1 year';
				$expMonth = '1 year';
			}

			$rules = <<<END
##### Optimal default expiration time
##### Optimal default expiration time - BEGIN
<IfModule mod_expires.c>
	# Enable expiration control
	ExpiresActive On
	
	# No caching for specific resource types
	## -- Application cache manifest
	ExpiresByType text/cache-manifest "now"
	## -- XML and JSON
	ExpiresByType application/json "now"
	ExpiresByType application/xml "now"
	ExpiresByType text/xml "now"

	## RSS and Atom feeds: 1 hour (hardcoded)
	ExpiresByType application/atom+xml "now plus 1 hour"
	ExpiresByType application/rss+xml "now plus 1 hour"

	# CSS and JS expiration: $expWeek after request
	ExpiresByType text/css "now plus $expWeek"
	ExpiresByType text/javascript "now plus $expWeek"
	ExpiresByType application/javascript "now plus $expWeek"
	ExpiresByType application/ld+json "now plus $expWeek"
	ExpiresByType application/x-javascript "now plus $expWeek"

	# Image files expiration: $expMonth after request
	ExpiresByType application/ico "now plus $expMonth"
	ExpiresByType application/smil "now plus $expMonth"
	ExpiresByType application/vnd.wap.wbxml "now plus $expMonth"
	ExpiresByType image/bmp "now plus $expMonth"
	ExpiresByType image/gif "now plus $expMonth"
	ExpiresByType image/ico "now plus $expMonth"
	ExpiresByType image/icon "now plus $expMonth"
	ExpiresByType image/jp2 "now plus $expMonth"
	ExpiresByType image/jpeg "now plus $expMonth"
	ExpiresByType image/jpg "now plus $expMonth"
	ExpiresByType image/pipeg "now plus $expMonth"
	ExpiresByType image/png "now plus $expMonth"
	ExpiresByType image/svg+xml "now plus $expMonth"
	ExpiresByType image/tiff "now plus $expMonth"
	ExpiresByType image/vnd.microsoft.icon "now plus $expMonth"
	ExpiresByType image/vnd.wap.wbmp "now plus $expMonth"
	ExpiresByType image/webp "now plus $expMonth"
	ExpiresByType image/x-icon "now plus $expMonth"
	ExpiresByType text/ico "now plus $expMonth"
	
	# Font files expiration: $expWeek after request
	ExpiresByType application/font-woff "now plus $expWeek"
	ExpiresByType application/font-woff2 "now plus $expWeek"
	ExpiresByType application/vnd.ms-fontobject "now plus $expWeek"
	ExpiresByType application/x-font-opentype "now plus $expWeek"
	ExpiresByType application/x-font-ttf "now plus $expWeek"
	ExpiresByType application/x-font-woff "now plus $expWeek"
	ExpiresByType font/opentype "now plus $expWeek"
	ExpiresByType font/otf "now plus $expWeek"
	ExpiresByType font/ttf "now plus $expWeek"
	ExpiresByType font/woff "now plus $expWeek"
	ExpiresByType font/woff2 "now plus $expWeek"

	# Audio files expiration: $expMonth after request
	ExpiresByType application/ogg "now plus $expMonth"
	ExpiresByType audio/3gpp "now plus $expMonth"
	ExpiresByType audio/3gpp2 "now plus $expMonth"
	ExpiresByType audio/aac "now plus $expMonth"
	ExpiresByType audio/basic "now plus $expMonth"
	ExpiresByType audio/mid "now plus $expMonth"
	ExpiresByType audio/midi "now plus $expMonth"
	ExpiresByType audio/mp3 "now plus $expMonth"
	ExpiresByType audio/mpeg "now plus $expMonth"
	ExpiresByType audio/ogg "now plus $expMonth"
	ExpiresByType audio/opus "now plus $expMonth"
	ExpiresByType audio/x-aiff "now plus $expMonth"
	ExpiresByType audio/x-mpegurl "now plus $expMonth"
	ExpiresByType audio/x-pn-realaudio "now plus $expMonth"
	ExpiresByType audio/x-wav "now plus $expMonth"
	ExpiresByType audio/wav "now plus $expMonth"

	# Movie files expiration: $expMonth after request
	ExpiresByType application/x-shockwave-flash "now plus $expMonth"
	ExpiresByType video/3gpp "now plus $expMonth"
	ExpiresByType video/3gpp2 "now plus $expMonth"
	ExpiresByType video/mp4 "now plus $expMonth"
	ExpiresByType video/mpeg "now plus $expMonth"
	ExpiresByType video/ogg "now plus $expMonth"
	ExpiresByType video/quicktime "now plus $expMonth"
	ExpiresByType video/webm "now plus $expMonth"
	ExpiresByType video/x-la-asf "now plus $expMonth"
	ExpiresByType video/x-ms-asf "now plus $expMonth"
	ExpiresByType video/x-msvideo "now plus $expMonth"
	ExpiresByType x-world/x-vrml "now plus $expMonth"
</IfModule>

# Disable caching of wp-admin/*.php
<Files "administrator/*.php">
	<IfModule mod_expires.c>
		ExpiresActive Off
	</IfModule>
	<IfModule mod_headers.c>
		Header unset ETag
		Header set Cache-Control "max-age=0, no-cache, no-store, must-revalidate"
		Header set Pragma "no-cache"
		Header set Expires "Wed, 11 Jan 1984 05:00:00 GMT"
	</IfModule>
</Files>

END;
			$htaccess->setOption('ExpTime', $rules);
		}

		$htaccess->setOption('HoggerAgents', null);

		if (!empty($config->hoggeragents) && ($config->nohoggers == 1))
		{
			$rules   = [];
			$rules[] = "##### Common hacking tools and bandwidth hoggers block";

			foreach ($config->hoggeragents as $agent)
			{
				$rules[] = "SetEnvIf user-agent \"(?i:$agent)\" stayout=1";
			}

			$rules[] = "<IfModule !mod_authz_core.c>";
			$rules[] = "deny from env=stayout";
			$rules[] = "</IfModule>";
			$rules[] = "<IfModule mod_authz_core.c>";
			$rules[] = "  <RequireAll>";
			$rules[] = "	Require all granted";
			$rules[] = "	Require not env stayout";
			$rules[] = "  </RequireAll>";
			$rules[] = "</IfModule>";

			$htaccess->setOption('HoggerAgents', $rules);
		}

		$htaccess->setOption('AutoCompress', null);
		$htaccess->setOption('ForceGzip', null);

		if (($config->autocompress == 1) && ($serverCaps->deflate))
		{
			// See https://stackoverflow.com/questions/5230202/apache-addoutputfilterbytype-is-deprecated-how-to-rewrite-using-mod-filter
			$apacheModuleForDeflate = version_compare($apacheVersion, '2.4', 'ge') ? 'mod_filter' : 'mod_deflate';
			$rules = <<<ENDHTCODE
##### Automatic compression of resources
# Automatically serve .css.gz, .css.br, .js.gz or .js.br instead of the original file
# These are versions of the files pre-compressed with GZip or Brotli, respectively
<IfModule mod_headers.c>
    # Serve Brotli compressed CSS files if they exist and the client accepts Brotli.
    RewriteCond "%{HTTP:Accept-encoding}" "br"
    RewriteCond "%{REQUEST_FILENAME}\.br" -s
    RewriteRule "^(.*)\.css" "$1\.css\.br" [QSA]

    # Serve Brotli compressed JS files if they exist and the client accepts Brotli.
    RewriteCond "%{HTTP:Accept-encoding}" "br"
    RewriteCond "%{REQUEST_FILENAME}\.br" -s
    RewriteRule "^(.*)\.js" "$1\.js\.br" [QSA]
    
    # Serve correct content types, and prevent double compression.
    RewriteRule "\.css\.br$" "-" [E=no-gzip:1]
    RewriteRule "\.css\.br$" "-" [T=text/css,E=no-brotli:1,L]
    RewriteRule "\.js\.br$" "-" [E=no-gzip:1]
    RewriteRule "\.js\.br$"  "-" [T=text/javascript,E=no-brotli:1,L]
    
    <FilesMatch "(\.js\.br|\.css\.br)$">
      # Serve correct encoding type.
      Header append Content-Encoding br

      # Force proxies to cache gzipped & non-gzipped css/js files separately.
      Header append Vary Accept-Encoding
    </FilesMatch>

    # Serve gzip compressed CSS files if they exist and the client accepts gzip.
    RewriteCond "%{HTTP:Accept-encoding}" "gzip"
    RewriteCond "%{REQUEST_FILENAME}\.gz" -s
    RewriteRule "^(.*)\.css" "$1\.css\.gz" [QSA]

    # Serve gzip compressed JS files if they exist and the client accepts gzip.
    RewriteCond "%{HTTP:Accept-encoding}" "gzip"
    RewriteCond "%{REQUEST_FILENAME}\.gz" -s
    RewriteRule "^(.*)\.js" "$1\.js\.gz" [QSA]

    # Serve correct content types, and prevent $apacheModuleForDeflate double gzip.
    # Also set it as the last rule to prevent the Front- or Backend protection from preventing access to the .gz file.
    RewriteRule "\.css\.gz$" "-" [E=no-brotli:1]
    RewriteRule "\.css\.gz$" "-" [T=text/css,E=no-gzip:1,L]
    RewriteRule "\.js\.gz$" "-" [E=no-brotli:1]
    RewriteRule "\.js\.gz$" "-" [T=text/javascript,E=no-gzip:1,L]

    <FilesMatch "(\.js\.gz|\.css\.gz)$">
      # Serve correct encoding type.
      Header append Content-Encoding gzip

      # Force proxies to cache gzipped & non-gzipped css/js files separately.
      Header append Vary Accept-Encoding
    </FilesMatch>
</IfModule>

## Automatically compress by MIME type using mod_brotli. Takes priority due to better compression ratio.
<IfModule mod_brotli.c>
	AddOutputFilterByType BROTLI_COMPRESS text/plain text/xml text/css application/xml application/xhtml+xml application/rss+xml application/javascript application/x-javascript image/svg+xml
</IfModule>

## Automatically compress by MIME type using {$apacheModuleForDeflate}.
<IfModule {$apacheModuleForDeflate}.c>
	AddOutputFilterByType DEFLATE text/plain text/xml text/css application/xml application/xhtml+xml application/rss+xml application/javascript application/x-javascript image/svg+xml
</IfModule>

## Fallback to mod_gzip when neither mod_brotli nor $apacheModuleForDeflate is available
<IfModule !mod_brotli.c>
	<IfModule !{$apacheModuleForDeflate}.c>
		<IfModule mod_gzip.c>
			mod_gzip_on Yes
			mod_gzip_dechunk Yes
			mod_gzip_keep_workfiles No
			mod_gzip_can_negotiate Yes
			mod_gzip_add_header_count Yes
			mod_gzip_send_vary Yes
			mod_gzip_min_http 1000
			mod_gzip_minimum_file_size 300
			mod_gzip_maximum_file_size 512000
			mod_gzip_maximum_inmem_size 60000
			mod_gzip_handle_methods GET
			mod_gzip_item_include file \.(html?|txt|css|js|php|pl|xml|rb|py|svg|scgz)$
			mod_gzip_item_include mime ^text/html$
			mod_gzip_item_include mime ^text/plain$
			mod_gzip_item_include mime ^text/xml$
			mod_gzip_item_include mime ^text/css$
			mod_gzip_item_include mime ^application/xml$
			mod_gzip_item_include mime ^application/xhtml+xml$
			mod_gzip_item_include mime ^application/rss+xml$
			mod_gzip_item_include mime ^application/javascript$
			mod_gzip_item_include mime ^application/x-javascript$
			mod_gzip_item_include mime ^image/svg+xml$
			mod_gzip_item_exclude rspheader ^Content-Encoding:.*gzip.*
			mod_gzip_item_include handler ^cgi-script$
			mod_gzip_item_include handler ^server-status$
			mod_gzip_item_include handler ^server-info$
			mod_gzip_item_include handler ^application/x-httpd-php
			mod_gzip_item_exclude mime ^image/.*
		</IfModule>
	</IfModule>
</IfModule>
ENDHTCODE;

			$htaccess->setOption('AutoCompress', $rules);

			if ($config->forcegzip == 1)
			{
				$rules = <<< HTACCESS
## Force GZip compression for mangled Accept-Encoding headers
<IfModule mod_setenvif.c>
	<IfModule mod_headers.c>
		SetEnvIfNoCase ^(Accept-EncodXng|X-cept-Encoding|X{15}|~{15}|-{15})$ ^((gzip|deflate)\s*,?\s*)+|[X~-]{4,13}$ HAVE_Accept-Encoding
		RequestHeader append Accept-Encoding "gzip,deflate" env=HAVE_Accept-Encoding
	</IfModule>
</IfModule>
HTACCESS;
				$htaccess->setOption('ForceGzip', $rules);
			}
		}

		$htaccess->setOption('EtagType', null);

		if ($config->etagtype != 'default')
		{
			$rules   = [];
			$rules[] = "## Send ETag (selected method: {$config->etagtype})";

			switch ($config->etagtype)
			{
				case 'full':
					$rules[] = "FileETag All";
					break;

				case 'sizetime':
					$rules[] = "FileETag MTime Size";
					break;

				case 'size':
					$rules[] = "FileETag Size";
					break;

				case 'none':
					$rules[] = <<< HTACCESS
<IfModule mod_headers.c>
	Header unset ETag
</IfModule>

FileETag None

HTACCESS;
					break;
			}

			$htaccess->setOption('EtagType', $rules);
		}

		$htaccess->setOption('AutoRoot', null);

		if ($config->autoroot)
		{
			$rules = <<<END
##### Redirect index.php to / -- BEGIN
RewriteCond %{THE_REQUEST} !^POST
RewriteCond %{THE_REQUEST} ^[A-Z]{3,9}\ /index\.php\ HTTP/
RewriteCond %{SERVER_PORT}>s ^(443>(s)|[0-9]+>s)$
RewriteRule ^index\.php$ http%2://{$config->httphost}/ $redirCode
##### Redirect index.php to / -- END

END;
			$htaccess->setOption('AutoRoot', $rules);
		}

		// If I have a rewriteBase condition, I have to append it here
		$subfolder = trim($config->rewritebase, '/') ? trim($config->rewritebase, '/') . '/' : '';

		$htaccess->setOption('wwwRedir', null);
		$rules = null;

		switch ($config->wwwredir)
		{
			// non-www to www
			case 1:
				if ($hasHSTS)
				{
					$rules = <<<END
##### Redirect non-www to www -- BEGIN
RewriteCond %{HTTP_HOST} !^www\. [NC]
RewriteRule ^(.*)$ https://www.%{HTTP_HOST}/$subfolder$1 $redirCode
##### Redirect non-www to www -- END


END;
				}
				else
				{
					{
						$rules = <<<END
##### Redirect non-www to www -- BEGIN
RewriteCond %{HTTP_HOST} !^www\. [NC]
RewriteCond %{HTTPS}>s ^(on>(s)|.*>s)$
RewriteRule ^(.*)$ http%2://www.%{HTTP_HOST}/$subfolder$1 $redirCode
##### Redirect non-www to www -- END


END;
					}
				}

				break;

			// www to non-www
			case 2:
				if ($hasHSTS)
				{
					$rules = <<<END
##### Redirect www to non-www -- BEGIN
RewriteCond %{HTTP_HOST} ^www\.(.+)$ [NC]
RewriteRule ^(.*)$ https://%1/$subfolder$1 $redirCode
##### Redirect www to non-www -- END


END;
				}
				else
				{
					$rules = <<<END
##### Redirect www to non-www -- BEGIN
# HTTP
RewriteCond %{HTTPS} !=on [OR]
RewriteCond %{HTTP:X-Forwarded-Proto} =http
RewriteCond %{HTTP_HOST} ^www\.(.+)$ [NC]
RewriteRule ^(.*)$ http://%1/$subfolder$1 $redirCode
# HTTPS
RewriteCond %{HTTPS} =on [OR]
RewriteCond %{HTTP:X-Forwarded-Proto} !=http
RewriteCond %{HTTP_HOST} ^www\.(.+)$ [NC]
RewriteRule ^(.*)$ https://%1/$subfolder$1 $redirCode
##### Redirect www to non-www -- END


END;
				}
				break;
		}

		$htaccess->setOption('wwwRedir', $rules);

		$htaccess->setOption('OldDomain', null);

		if (!empty($config->olddomain))
		{
			$rules   = [];
			$rules[] = "##### Redirect old to new domain";

			$domains        = trim($config->olddomain);
			$domains        = explode(',', $domains);
			$newHTTPDomain  = $config->httphost;
			$newHTTPSDomain = $config->httpshost;

			foreach ($domains as $olddomain)
			{
				$olddomain = trim($olddomain);

				if (empty($olddomain))
				{
					continue;
				}

				$httpRedirect  = $olddomain != $newHTTPDomain;
				$httpsRedirect = $olddomain != $newHTTPSDomain;
				$olddomain     = $this->escape_string_for_regex($olddomain);

				if ($httpRedirect && !$hasHSTS)
				{
					$rules[] = <<<END
## Plain HTTP
RewriteCond %{HTTPS} !=on [OR]
RewriteCond %{HTTP:X-Forwarded-Proto} =http
RewriteCond %{HTTP_HOST} ^$olddomain [NC]
RewriteRule (.*) http://$newHTTPDomain/$1 $redirCode

END;
				}

				if ($httpsRedirect && !$hasHSTS)
				{
					$rules[] = <<<END
## HTTPS
RewriteCond %{HTTPS} =on [OR]
RewriteCond %{HTTP:X-Forwarded-Proto} !=http
RewriteCond %{HTTP_HOST} ^$olddomain [NC]
RewriteRule (.*) https://$newHTTPSDomain/$1 $redirCode

END;
				}

				if ($httpsRedirect && $hasHSTS)
				{
					$rules[] = <<<END
## Forced HTTPS - You have enabled the HSTS feature
RewriteCond %{HTTP_HOST} ^$olddomain [NC]
RewriteRule (.*) https://$newHTTPSDomain/$1 $redirCode

END;

				}
			}

			$htaccess->setOption('OldDomain', $rules);
		}

		$htaccess->setOption('HTTPSurls', null);

		if (!empty($config->httpsurls))
		{
			$rules   = [];
			$rules[] = "##### Force HTTPS for certain pages";

			foreach ($config->httpsurls as $url)
			{
				$urlesc = '^' . $this->escape_string_for_regex($url) . '$';

				$rules[] = "RewriteCond %{HTTPS} ^off$ [NC,OR]";
				$rules[] = "RewriteCond %{HTTP:X-Forwarded-Proto} =http";
				$rules[] = "RewriteRule $urlesc https://{$config->httpshost}/$url $redirCode";
			}

			$htaccess->setOption('HTTPSurls', $rules);
		}

		$rules = <<<END
##### Rewrite rules to block out some common exploits
RewriteCond %{QUERY_STRING} proc/self/environ [OR]
RewriteCond %{QUERY_STRING} base64_(en|de)code\(.*\) [OR]
RewriteCond %{QUERY_STRING} (<|%3C).*script.*(>|%3E) [NC,OR]
RewriteCond %{QUERY_STRING} GLOBALS(=|\[|\%[0-9A-Z]{0,2}) [OR]
RewriteCond %{QUERY_STRING} _REQUEST(=|\[|\%[0-9A-Z]{0,2})
RewriteRule .* index.php [F]

END;
		$htaccess->setOption('CommonExploits', $rules);

		$htaccess->setOption('FileInj', null);

		if ($config->fileinj == 1)
		{
			$rules = <<<END
##### File injection protection
RewriteCond %{REQUEST_METHOD} GET
RewriteCond %{QUERY_STRING} [a-zA-Z0-9_]=http[s]?:// [OR]
RewriteCond %{QUERY_STRING} [a-zA-Z0-9_]=(\.\.//?)+ [OR]
RewriteCond %{QUERY_STRING} [a-zA-Z0-9_]=/([a-z0-9_.]//?)+ [NC]
RewriteRule .* - [F]

END;
			$htaccess->setOption('FileInj', $rules);
		}

		// $htaccess .= "##### Advanced server protection rules exceptions -- BEGIN\n";

		$htaccess->setOption('ExceptionFiles', null);

		if (empty($config->exceptionfiles))
		{
			$config->exceptionfiles = [];
		}

		if (!in_array('installation/index.php', $config->exceptionfiles))
		{
			$config->exceptionfiles[] = 'installation/index.php';
		}

		if (!in_array('kickstart.php', $config->exceptionfiles))
		{
			$config->exceptionfiles[] = 'kickstart.php';
		}

		if (!empty($config->exceptionfiles))
		{
			$rules = [];

			foreach ($config->exceptionfiles as $file)
			{
				$file = '^' . $this->escape_string_for_regex($file) . '$';
				$rules[] = <<<END
RewriteRule $file - [L]

END;
			}

			$htaccess->setOption('ExceptionFiles', $rules);
		}

		$htaccess->setOption('ExceptionDirs', null);

		if (empty($config->exceptiondirs))
		{
			$config->exceptiondirs = [];
		}

		if (!in_array('installation', $config->exceptiondirs))
		{
			$config->exceptiondirs[] = 'installation';
		}

		if (!empty($config->exceptiondirs))
		{
			$rules = [];

			foreach ($config->exceptiondirs as $dir)
			{
				$dir = trim($dir, '/');
				$dir = $this->escape_string_for_regex($dir);
				$rules[] = <<<END
RewriteCond %{REQUEST_FILENAME} !(\.php)$
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule ^$dir/ - [L]

END;
			}

			$htaccess->setOption('ExceptionDirs', $rules);
		}

		$htaccess->setOption('FullaccessDirs', null);

		if (!empty($config->fullaccessdirs))
		{
			$rules = [];

			foreach ($config->fullaccessdirs as $dir)
			{
				$dir = trim($dir, '/');
				$dir = $this->escape_string_for_regex($dir);
				$rules[] = <<<END
RewriteRule ^$dir/ - [L]

END;
			}

			$htaccess->setOption('FullaccessDirs', $rules);
		}

		//$htaccess .= "##### Advanced server protection rules exceptions -- END\n\n";

		//$htaccess .= "##### Advanced server protection -- BEGIN\n\n";

		$htaccess->setOption('PHPeaster', null);

		if ($config->phpeaster == 1)
		{
			$rules = <<<END
RewriteCond %{QUERY_STRING} \=PHP[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12} [NC]
RewriteRule .* - [F]

END;
			$htaccess->setOption('PHPeaster', $rules);
		}

		$htaccess->setOption('SiteProt', null);

		if ($config->siteprot == 1)
		{
			$fedirs  = implode('|', $config->exdirs);
			$fetypes = implode('|', $config->extypes);

			if (empty($fetypes))
			{
				$fetypes = '7z|appcache|atom|avi|bbaw|bmp|crx|css|cur|doc|docx|eot|f4a|f4b|f4p|f4v|flv|geojson|gif|htc|htm|html|ico|jpeg|jpe|jpg|jp2|jpe2|js|json|jsonl|jsond|m4a|m4v|manifest|map|mkv|mp3|mp4|mpg|mpeg|ods|odp|odt|oex|oga|ogg|ogv|opus|otf|png|pdf|png|ppt|pptx|rar|rdf|rss|safariextz|svg|svgz|swf|tar|topojson|tbz|tbz2|tgz|ttc|ttf|txt|txz|vcard|vcf|vtt|wav|webapp|webm|webp|woff|woff2|xloc|xls|xlsx|xml|xpi|xps|xz|zip|xsl|7Z|APPCACHE|ATOM|AVI|BBAW|BMP|CRX|CSS|CUR|DOC|DOCX|EOT|F4A|F4B|F4P|F4V|FLV|GEOJSON|GIF|HTC|HTM|HTML|ICO|JPEG|JPE|JPG|JP2|JPE2|JS|JSON|JSONL|JSOND|M4A|M4V|MANIFEST|MAP|MKV|MP3|MP4|MPG|MPEG|ODS|ODP|ODT|OEX|OGA|OGG|OGV|OPUS|OTF|PNG|PDF|PNG|PPT|PPTX|RAR|RDF|RSS|SAFARIEXTZ|SVG|SVGZ|SWF|TAR|TOPOJSON|TBZ|TBZ2|TGZ|TTC|TTF|TXT|TXZ|VCARD|VCF|VTT|WAV|WEBAPP|WEBM|WEBP|WOFF|WOFF2|XLOC|XLS|XLSX|XML|XPI|XPS|XZ|ZIP|XSL';
			}

			$rules   = <<<END
### Allow access to public area files
RewriteRule ^index\.php$ - [L]
RewriteRule ^wp-login\.php$ - [L]

END;

			$rules .= <<<END
### Allow access to wp-admin files
RewriteRule ^wp-admin/?$ - [L]
RewriteRule ^wp-admin/[a-zA-Z0-9-]{1,}\.php$ - [L]
RewriteRule ^wp-admin/maint/[a-zA-Z0-9-]{1,}\.php$ - [L]
RewriteRule ^wp-admin/network/[a-zA-Z0-9-]{1,}\.php$ - [L]
RewriteRule ^wp-admin/user/[a-zA-Z0-9-]{1,}\.php$ - [L]

### Allow access to Admin Tools frontend scanner
RewriteRule ^wp-content/plugins/admintoolswp/filescanner\.php$ - [L] 

### Allow access to static media files inside WordPress' known directories
RewriteRule ^wp-(admin|content|includes)/.*\.($fetypes)$ - [L]
RewriteRule ^wp-includes/js/tinymce/wp-tinymce.php$ - [L]

## Explicitly allow access to the site's index.php main entry point file
RewriteRule ^index\.php(/.*){0,1}$ - [L]

## Explicitly allow access to the site's robots.txt file
RewriteRule ^robots\.txt$ - [L]

## Disallow access to rogue PHP files throughout the site, unless they are explicitly allowed
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule (.*\.php)$ - [F]

END;

			if ($fedirs && $fetypes)
			{
				$rules .= <<<END
## Allow limited access for certain WordPress system directories with client-accessible content
RewriteRule ^($fedirs)/.*\.($fetypes)$ - [L]
RewriteRule ^($fedirs)/ - [F]

END;
			}

			if ($config->staticrisks == 1)
			{
				$static_dirs = '';

				if ($fedirs)
				{
					$static_dirs = sprintf("/(%s)/", $fedirs);
				}

				$rules .= <<< END
#### Disable client-side risky behavior in frontend static content
<If "%{REQUEST_URI} =~ m#^$static_dirs.*\.($fetypes)$#">
    <IfModule mod_headers.c>
        Header always set Content-Security-Policy "default-src 'self'; script-src 'none';"
    </IfModule>
</If>

END;
				$rules .= "##### Advanced server protection rules exceptions also bypass the “disable client-side risky behavior” features -- BEGIN\n";

				foreach ($config->exceptionfiles as $file)
				{
					$file   = ltrim($file, '/');
					$rules .= <<<END
<If "%{REQUEST_URI} == '/$file'">
    <IfModule mod_headers.c>
        Header always unset Content-Security-Policy
    </IfModule>
</If>

END;
				}

				foreach ($config->exceptiondirs as $dir)
				{
					$dir    = trim($dir, '/');
					$dir    = $this->escape_string_for_regex($dir);
					$rules .= <<<END
<If "%{REQUEST_URI} =~ m#^$dir/#">
    <IfModule mod_headers.c>
        Header always unset Content-Security-Policy
    </IfModule>
</If>

END;
				}

				foreach ($config->fullaccessdirs as $dir)
				{
					$dir    = trim($dir, '/');
					$dir    = $this->escape_string_for_regex($dir);
					$rules .= <<<END
<If "%{REQUEST_URI} =~ m#^$dir/#">
    <IfModule mod_headers.c>
        Header always unset Content-Security-Policy
    </IfModule>
</If>

END;
				}

				$rules .= "##### Advanced server protection rules exceptions also bypass the “disable client-side risky behavior” features -- END\n\n";
		}

			$htaccess->setOption('SiteProt', $rules);
		}

		$htaccess->setOption('Leftovers', null);

		if ($config->leftovers == 1)
		{
			$rules = <<<END
## Disallow access to htaccess.txt, .user.ini, php.ini and configuration.php-dist
RewriteRule ^(htaccess\.txt|wp-config-sample\.php|php\.ini|license\.txt|readme\.html|\.user\.ini)$ - [F]

END;
			$htaccess->setOption('Leftovers', $rules);
		}

		$htaccess->setOption('SiteProtPart2', null);

		if ($config->siteprot == 1)
		{
			$rules = <<<END
# Disallow access to all other front-end folders
RewriteCond %{REQUEST_FILENAME} -d
RewriteCond %{REQUEST_URI} !^/
RewriteRule .* - [F]

# Disallow access to all other front-end files
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule !^index.php$ - [F]

END;
			$htaccess->setOption('SiteProtPart2', $rules);

		}

		$htaccess->setOption('Clickjacking', null);

		if ($config->clickjacking == 1)
		{
			$action = version_compare($apacheVersion, '2.0', 'ge') ? 'always append' : 'append';
			$rules = <<< ENDCONF

## Protect against clickjacking
<IfModule mod_headers.c>

	Header $action X-Frame-Options SAMEORIGIN

	# The `X-Frame-Options` response header should be send only for
	# HTML documents and not for the other resources.

	<FilesMatch "\.(appcache|atom|bbaw|bmp|crx|css|cur|eot|f4[abpv]|flv|geojson|gif|htc|ico|jpe?g|js|json(ld)?|m4[av]|manifest|map|mp4|oex|og[agv]|opus|otf|pdf|png|rdf|rss|safariextz|svgz?|swf|topojson|tt[cf]|txt|vcard|vcf|vtt|webapp|web[mp]|woff2?|xloc|xml|xpi)$">
		Header unset X-Frame-Options
	</FilesMatch>

</IfModule>

ENDCONF;
			$htaccess->setOption('Clickjacking', $rules);
		}

		$htaccess->setOption('ReduceMIMEtypeRisks', null);

		if ($config->reducemimetyperisks == 1)
		{
			$rules = <<< HTACCESS
## Reduce MIME type security risks
<IfModule mod_headers.c>
	Header set X-Content-Type-Options "nosniff"
</IfModule>

HTACCESS;
			$htaccess->setOption('ReduceMIMEtypeRisks', $rules);
		}

		$htaccess->setOption('ReflectedXSS', null);

		if ($config->reflectedxss == 1)
		{
			$rules = <<< HTACCESS
## Reflected XSS prevention
<IfModule mod_headers.c>
Header set X-XSS-Protection "1; mode=block"
</IfModule>

# mod_headers cannot match based on the content-type, however,
# the X-XSS-Protection response header should be send only for
# HTML documents and not for the other resources.

<IfModule mod_headers.c>
	<FilesMatch "\.(appcache|atom|bbaw|bmp|crx|css|cur|eot|f4[abpv]|flv|geojson|gif|htc|ico|jpe?g|js|json(ld)?|m4[av]|manifest|map|mp4|oex|og[agv]|opus|otf|pdf|png|rdf|rss|safariextz|svgz?|swf|topojson|tt[cf]|txt|vcard|vcf|vtt|webapp|web[mp]|webmanifest|woff2?|xloc|xml|xpi)$">
		Header unset X-XSS-Protection
	</FilesMatch>
</IfModule>

HTACCESS;
			$htaccess->setOption('ReflectedXSS', $rules);
		}

		$htaccess->setOption('NoServerSignature', null);

		if ($config->noserversignature == 1)
		{
			$rules = <<< HTACCESS
## Remove Apache and PHP version signature
<IfModule mod_headers.c>
	Header always unset X-Powered-By
	Header always unset X-Content-Powered-By
</IfModule>

ServerSignature Off

HTACCESS;
			$htaccess->setOption('NoServerSignature', $rules);
		}

		$htaccess->setOption('NoTransform', null);

		if ($config->notransform == 1)
		{
			$rules = <<< HTACCESS
## Prevent content transformation
<IfModule mod_headers.c>
	Header merge Cache-Control "no-transform"
</IfModule>

HTACCESS;
			$htaccess->setOption('NoTransform', $rules);
		}

		//$htaccess .= "##### Advanced server protection -- END\n\n";

		$htaccess->setOption('HSTS', null);

		if ($hasHSTS)
		{
			$action = version_compare($apacheVersion, '2.0', 'ge') ? 'always set' : 'set';
			$rules = <<<END
## HSTS Header - See http://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security
<IfModule mod_headers.c>
Header $action Strict-Transport-Security "max-age=31536000" env=HTTPS
</IfModule>

END;
			$htaccess->setOption('HSTS', $rules);
		}

		$htaccess->setOption('NoTraceTrack', null);

		if ($config->notracetrack == 1)
		{
			$tmpRedirCode = $serverCaps->customCodes ? '[R=405,L]' : '[F,L]';
			$rules = <<<END
## Disable HTTP methods TRACE and TRACK (protect against XST)
RewriteCond %{REQUEST_METHOD} ^TRACE
RewriteRule ^ - $tmpRedirCode

END;
			$htaccess->setOption('NoTraceTrack', $rules);
		}

		$htaccess->setOption('CORS', null);

		if ($config->cors == 1)
		{
			$action = version_compare($apacheVersion, '2.0', 'ge') ? 'always set' : 'set';
			$rules = <<<END
## Cross-Origin Resource Sharing (CORS) -- See http://enable-cors.org/
<IfModule mod_headers.c>
	Header $action Access-Control-Allow-Origin "*"
	Header $action Timing-Allow-Origin "*"
</IfModule>

END;
			$htaccess->setOption('CORS', $rules);
		}
		elseif ($config->cors == -1)
		{
			$action = version_compare($apacheVersion, '2.0', 'ge') ? 'always set' : 'set';
			$rules = <<<END
## Explicitly disable Cross-Origin Resource Sharing (CORS) -- See http://enable-cors.org/
<IfModule mod_headers.c>
	Header $action Cross-Origin-Resource-Policy "same-origin"
</IfModule>

END;
			$htaccess->setOption('CORS', $rules);
		}

		$htaccess->setOption('RefererPolicy', null);

		if ($config->referrerpolicy !== '-1')
		{
			$action = version_compare($apacheVersion, '2.0', 'ge') ? 'always set' : 'set';
			$rules = <<<END
## Referrer-policy
<IfModule mod_headers.c>
	Header $action Referrer-Policy "{$config->referrerpolicy}"
</IfModule>

END;
			$htaccess->setOption('RefererPolicy', $rules);
		}

		$htaccess->setOption('UTF8charset', null);

		if ($config->utf8charset == 1)
		{
			$rules = <<<END
## Set the UTF-8 character set as the default
#  Serve all resources labeled as `text/html` or `text/plain`
#  with the media type `charset` parameter set to `UTF-8`.

AddDefaultCharset utf-8

# Serve the following file types with the media type `charset`
# parameter set to `UTF-8`.
#
# https://httpd.apache.org/docs/current/mod/mod_mime.html#addcharset

<IfModule mod_mime.c>
	AddCharset utf-8 .atom \
					 .bbaw \
					 .css \
					 .geojson \
					 .js \
					 .json \
					 .jsonld \
					 .rdf \
					 .rss \
					 .topojson \
					 .vtt \
					 .webapp \
					 .xloc \
					 .xml
</IfModule>

END;
			$htaccess->setOption('UTF8charset', $rules);
		}

		$htaccess->setOption('CustFoot', null);

		if ($config->custfoot)
		{
			$htaccess->setOption('CustFoot', $config->custfoot);
		}

		$new_htaccess = $htaccess->updateFile(false);

		return $new_htaccess;
	}

	/**
	 * Guesses and returns the Apache version family.
	 *
	 * @return  string  1.1, 1.3, 2.0, 2.2, 2.5 or 0.0 (if no match)
	 */
	private function apacheVersion()
	{
		// Get the server string
		$serverString = $_SERVER['SERVER_SOFTWARE'];

		// Not defined? Assume Apache 2.0.
		if (empty($serverString))
		{
			return '2.0';
		}

		// LiteSpeed? Fake it.
		if (strtoupper(substr($serverString, 0, 9)) == 'LITESPEED')
		{
			return '2.0';
		}

		// Not Apache? Return 0.0
		if (strtoupper(substr($serverString, 0, 6)) !== 'APACHE')
		{
			return '0.0';
		}

		// No slash after Apache? Assume 2.5
		if (strlen($serverString) < 7)
		{
			return '2.5';
		}

		if (substr($serverString, 6, 1) != '/')
		{
			return '2.5';
		}

		// Strip part past the version string
		$serverString = substr($serverString, 7);

		$v = substr($serverString, 0, 3);
		switch ($v)
		{
			case '1.3':
			case '2.0':
			case '2.2':
			case '2.5':
				return $v;

				break;

			default:
				if (version_compare($v, '1.3', 'lt'))
				{
					return '1.1';
				}
				else
				{
					return '2.2';
				}

				break;
		}
	}
}
