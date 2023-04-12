<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

if (!defined('AKEEBA_BACKUP_ORIGIN'))
{
	define('AKEEBA_BACKUP_ORIGIN', 'cli');
}

AkeebaBackupWP::boot('boot_wpcli.php');

try
{
	WP_CLI::add_command('akeeba', 'Akeeba\\WPCLI\\Command\\NamespaceDescription');
	WP_CLI::add_command('akeeba backup', 'Akeeba\\WPCLI\\Command\\Backup');
	WP_CLI::add_command('akeeba filter', 'Akeeba\\WPCLI\\Command\\Filter');
	WP_CLI::add_command('akeeba log', 'Akeeba\\WPCLI\\Command\\Log');
	WP_CLI::add_command('akeeba option', 'Akeeba\\WPCLI\\Command\\Option');
	WP_CLI::add_command('akeeba profile', 'Akeeba\\WPCLI\\Command\\Profile');
	WP_CLI::add_command('akeeba sysconfig', 'Akeeba\\WPCLI\\Command\\Sysconfig');
}
catch (Exception $e)
{
	echo "Could not register Akeeba Backup commands for WP-CLI.\n";
	echo "Error message: {$e->getMessage()}\n\n";
}
