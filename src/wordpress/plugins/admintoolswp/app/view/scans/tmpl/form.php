<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */
use Akeeba\AdminTools\Admin\Helper\Language;

/** @var $this \Akeeba\AdminTools\Admin\View\Scans\Html */

defined('ADMINTOOLSINC') or die;
?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=Scans">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
	<?php echo Language::_('COM_ADMINTOOLS_TITLE_SCANS_COMMENT');?>
</h1>

<section class="akeeba-panel">
	<form action="<?php echo ADMINTOOLSWP_URL; ?>&view=Scans" method="post" class="akeeba-form--horizontal">
        <div class="akeeba-form-group">
            <label><?php echo Language::_('COM_ADMINTOOLS_SCANS_EDIT_COMMENT')?></label>
            <div>
                <textarea name="comment" style="height: 150px"><?php echo $this->item->comment?></textarea>
            </div>
        </div>

        <p class="submit">
			<input type="submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SAVE')?>"/>
			<a class="akeeba-btn--ghost" href="<?php echo ADMINTOOLSWP_URL; ?>&view=ScanAlerts&scan_id=<?php echo $this->item->id?>">
				<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_CANCEL') ?>
			</a>
		</p>

        <input type="hidden" name="view" value="Scans"/>
        <input type="hidden" name="task" value="save"/>
        <input type="hidden" name="id" value="<?php echo isset($this->item) ? $this->item->id : '' ?>" />
		<?php wp_nonce_field('postScans') ?>
	</form>
</section>
