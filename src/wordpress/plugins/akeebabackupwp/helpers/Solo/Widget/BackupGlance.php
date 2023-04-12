<?php

namespace Solo\Widget;

use Solo\Application;
use Solo\Helper\Status;

class BackupGlance
{
	public static function display()
	{
		if (!defined('AKEEBABACKUP_PRO'))
		{
			echo "Akeeba Backup is not properly installed, or has been disabled.";

			return;
		}

		$darkMode = self::initThemeing();

		?>
<div id="akeeba-backup-widget-backup-glance"
     class="akeeba-renderer-fef<?= ($darkMode == 1) ? ' akeeba-renderer-fef--dark' : '' ?>"
	 style="margin: 0; padding: 0"
>
<?php
		echo Status::getInstance()->getLatestBackupDetails();
		?>
</div>
	<?php
	}

	protected static function initThemeing()
	{
		$app       = Application::getInstance();
		$container = $app->getContainer();
		$darkMode  = $container->appConfig->get('darkmode', -1);

		wp_enqueue_style('fef.min.css', \AkeebaBackupWP::$pluginUrl . '/app/media/css/fef.min.css');
		wp_enqueue_style('fef-wp.min.css', \AkeebaBackupWP::$pluginUrl . '/app/media/css/fef-wp.min.css', [
			'fef.min.css'
		]);
		wp_enqueue_style('theme.min.css', \AkeebaBackupWP::$pluginUrl . '/app/media/css/theme.min.css', [
			'fef-wp.min.css',
			'fef.min.css'
		]);

		if ($darkMode)
		{
			wp_enqueue_style('dark.min.css', \AkeebaBackupWP::$pluginUrl . '/app/media/css/dark.min.css', [
				'fef-wp.min.css',
				'fef.min.css'
			]);
			wp_enqueue_style('theme_dark.min.css', \AkeebaBackupWP::$pluginUrl . '/app/media/css/theme_dark.min.css', [
				'theme.min.css',
			]);
		}

		return $darkMode;
	}
}