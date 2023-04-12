<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;

/** @var $this \Akeeba\AdminTools\Admin\View\BadWords\Html */

defined('ADMINTOOLSINC') or die;

?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=BadWords">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
	<?php echo Language::_('COM_ADMINTOOLS_TITLE_BADWORDS_EDIT');?>
</h1>

<section class="akeeba-panel">
	<form action="<?php echo ADMINTOOLSWP_URL; ?>&view=BadWords" method="post" class="akeeba-form--horizontal">
        <div class="akeeba-form-group">
            <label for="word"><?php echo Language::_('COM_ADMINTOOLS_LBL_BADWORD_WORD'); ?></label>
            <div>
                <input type="text" name="word" id="word" value="<?php echo isset($this->item) ? $this->escape($this->item->word) : '' ?>" />
            </div>
        </div>

		<p class="submit">
			<input type="submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SAVE')?>"/>
			<a class="akeeba-btn--ghost" href="<?php echo ADMINTOOLSWP_URL; ?>&view=BadWords">
                <?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_CANCEL') ?>
            </a>
		</p>

        <input type="hidden" name="view" value="BadWords"/>
        <input type="hidden" name="task" value="save"/>
        <input type="hidden" name="id" value="<?php echo isset($this->item) ? $this->item->id : '' ?>" />
		<?php wp_nonce_field('postBadWords') ?>
	</form>
</section>
