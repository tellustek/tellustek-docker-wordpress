<?php

namespace Solo\Widget;

use Awf\Text\Text;
use Awf\Uri\Uri;
use Solo\Application;

class QuickBackup extends BackupGlance
{
	public static function display()
	{
		if (!defined('AKEEBABACKUP_PRO'))
		{
			echo "Akeeba Backup is not properly installed, or has been disabled.";

			return;
		}

		\AkeebaBackupWP::boot('boot_widget.php');
		$app       = Application::getInstance();
		$container = $app->getContainer();
		$appConfig = $container->appConfig;
		$profileId = $appConfig->get('quickbackup_profile', 1);

		$darkMode = self::initThemeing();

		?>
		<div id="akeeba-backup-widget-backup-glance"
			 class="akeeba-renderer-fef<?= ($darkMode == 1) ? ' akeeba-renderer-fef--dark' : '' ?>"
			 style="margin: 0; padding: 0"
		>
			<div class="akeeba-grid">
				<form action="<?= AKEEBA_SOLO_WP_URL ?>&view=backup"
					  method="post"
					  id="akeebabackup-quickbackup-form"
				>
					<a class="oneclick akeeba-action--teal"
					   href="javascript:document.getElementById('akeebabackup-quickbackup-form').submit();"
					>
						<span class="akion-play"></span>
						<span><?= Text::_('COM_AKEEBA_BACKUP') ?></span>
					</a>

					<input type="hidden" name="autostart" value="1" />
					<input type="hidden" name="profile" value="<?= $profileId ?>" />
					<input type="hidden" name="returnurl" value="<?= base64_encode(Uri::current()) ?>">
					<input type="hidden" name="<?= $container->session->getCsrfToken()->getValue() ?>" value="1" />
				</form>
			</div>
		</div>
		<?php

	}

}