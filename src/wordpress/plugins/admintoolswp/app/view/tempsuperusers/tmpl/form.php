<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Library\Date\Date;

/** @var $this \Akeeba\AdminTools\Admin\View\BadWords\Html */

defined('ADMINTOOLSINC') or die;

$expiration = new Date($this->item->expiration);

?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=TempSuperUsers">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
	<?php echo Language::_('COM_ADMINTOOLS_TITLE_TEMPSUPERUSERS_EDIT');?>
</h1>

<section class="akeeba-panel">
	<form action="<?php echo ADMINTOOLSWP_URL; ?>&view=TempSuperUsers" method="post" class="akeeba-form--horizontal">
        <div class="akeeba-container--50-50">
            <div>
                <div class="akeeba-form-group">
                    <label for="dummy">
                        <?php echo Language::_('COM_ADMINTOOLS_LBL_TEMPSUPERUSER_EDITINGUSER') ?>
                    </label>
                    <p>
                        <strong><?php echo $this->item->wp->user_login ?></strong><br />
                        <?php echo $this->item->wp->display_name ?>
                        <em> (<?php echo $this->item->wp->user_email ?>) </em>
                    </p>
                </div>
                <div class="akeeba-form-group">
                    <label for="expiration">
                        <?php echo Language::_('COM_ADMINTOOLS_TEMPSUPERUSERS_FIELD_EXPIRATION') ?>
                    </label>
                    <input type="date" id="expiration" name="expiration" value="<?php echo $expiration->format('Y-m-d') ?>" />
                </div>
            </div>
        </div>

		<p class="submit">
			<input type="submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SAVE')?>"/>
			<a class="akeeba-btn--ghost" href="<?php echo ADMINTOOLSWP_URL; ?>&view=TempSuperUsers">
                <?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_CANCEL') ?>
            </a>
		</p>

        <input type="hidden" name="view" value="TempSuperUsers"/>
        <input type="hidden" name="task" value="save"/>
        <input type="hidden" name="id" value="<?php echo isset($this->item) ? $this->item->id : '' ?>" />
        <input type="hidden" name="user_id" value="<?php echo $this->item->user_id ?>" />
		<?php wp_nonce_field('postTempSuperUsers') ?>
	</form>
</section>
