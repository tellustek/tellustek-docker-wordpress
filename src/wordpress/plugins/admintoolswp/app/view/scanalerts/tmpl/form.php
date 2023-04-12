<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Html;
use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Library\Html\Select as SelectHelper;

/** @var $this Akeeba\AdminTools\Admin\View\ScanAlerts\Html */

defined('ADMINTOOLSINC') or die;

?>
<h1>
	<a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=Scans">
		<span class="akion-chevron-left"></span>
		<span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS') ?>
	</a>
	<?php echo Language::sprintf('COM_ADMINTOOLS_TITLE_SCANALERT_EDIT', $this->item->scan_id); ?>
</h1>

<section class="akeeba-panel">
	<form name="adminForm" id="adminForm" action="<?php echo ADMINTOOLSWP_URL; ?>&view=ScanAlerts" method="post"
		  class="akeeba-form--horizontal">
		<h3><?php echo Language::_('COM_ADMINTOOLS_LBL_SCANALERT_FILEINFO'); ?></h3>

		<div class="akeeba-form-group">
			<label><?php echo Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_PATH'); ?></label>
			<div>
				<?php echo $this->item->path ?>
			</div>
		</div>

		<div class="akeeba-form-group">
			<label><?php echo Language::_('COM_ADMINTOOLS_LBL_SCANALERT_SCANDATE'); ?></label>
			<div>
				<?php echo $this->scanDate->format('l, d F Y H:i') ?>
			</div>
		</div>

		<div class="akeeba-form-group">
			<label><?php echo Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS'); ?></label>
			<div>
                <span
						class="admintools-scanfile-<?php echo $this->fstatus ?> <?php if (!$this->item->threat_score): ?>admintools-scanfile-nothreat<?php endif ?>">
                        <?php echo Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS_' . $this->fstatus) ?>
                    </span>
			</div>
		</div>

		<div class="akeeba-form-group">
			<label><?php echo Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_THREAT_SCORE'); ?></label>
			<div>
                <span class="admintools-scanfile-threat-<?php echo $this->threatindex ?>">
                        <?php echo $this->item->threat_score ?>
                    </span>
			</div>
		</div>

		<div class="akeeba-form-group">
			<label><?php echo Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_ACKNOWLEDGED'); ?></label>
			<div>
				<?php echo SelectHelper::booleanswitch('acknowledged', $this->item->acknowledged); ?>
			</div>
		</div>

		<p class="submit">
			<input type="submit" class="akeeba-btn--primary"
				   value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SAVE') ?>" />

			<a class="akeeba-btn--ghost"
			   href="<?php echo ADMINTOOLSWP_URL; ?>&view=ScanAlerts&scan_id=<?php echo $this->item->scan_id ?>">
				<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_CANCEL') ?>
			</a>
		</p>

		<div>
			<?php if ($this->generateDiff && ($this->fstatus == 'modified')): ?>
				<h3><?php echo Language::_('COM_ADMINTOOLS_LBL_SCANALERT_DIFF') ?></h3>

				<button id="show-diff">
					<?php echo Language::_('COM_ADMINTOOLS_LBL_SCANALERT_SHOWDIFF'); ?>
				</button>

				<div id="admintools-diff-contents" style="display: none;">
					<pre><code class="<?php echo $this->suspiciousFile ? 'php' : 'diff' ?>"><?php echo htmlentities($this->item->diff); ?></code></pre>
				</div>

			<?php endif; ?>
			<h3><?php echo Language::_('COM_ADMINTOOLS_LBL_SCANALERT_SOURCE') ?></h3>

			<button id="show-contents" class="akeeba-btn--dark">
				<?php echo Language::_('COM_ADMINTOOLS_LBL_SCANALERT_SHOWCONTENT') ?>
			</button>

			<div id="admintools-file-contents" style="display:none">
				<table class="form-table">
					<tr>
						<th><?php echo Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_MD5'); ?></th>
						<td><?php echo @md5_file(ABSPATH . '/' . $this->item->path) ?></td>
					</tr>
				</table>

				<pre><?php echo Html::getFileSourceForDisplay($this->item->path, true); ?></pre>
			</div>

		</div>

		<input type="hidden" name="view" value="ScanAlerts" />
		<input type="hidden" name="task" value="save" />
		<input type="hidden" name="admintools_scanalert_id"
			   value="<?php echo $this->item->admintools_scanalert_id ?>" />
		<?php wp_nonce_field('postScanAlerts') ?>
	</form>
</section>

<script type="application/javascript">
    jQuery(document).ready(function ($) {
        hljs.initHighlightingOnLoad();
    });
</script>
