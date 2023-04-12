<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;

/** @var $this \Akeeba\AdminTools\Admin\View\WhitelistedAddresses\Html */

defined('ADMINTOOLSINC') or die;

?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=WhitelistedAddresses">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
        <?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
    <?php echo Language::_('COM_ADMINTOOLS_TITLE_IPWL_EDIT');?>
</h1>

<section class="akeeba-panel">
    <div class="akeeba-block--info">
        <p><?php echo Language::_('COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_IP_INTRO') ?></p>
        <ol>
            <li><?php echo Language::_('COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_IP_OPT1') ?></li>
            <li><?php echo Language::_('COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_IP_OPT2') ?></li>
            <li><?php echo Language::_('COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_IP_OPT3') ?></li>
            <li><?php echo Language::_('COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_IP_OPT4') ?></li>
            <li><?php echo Language::_('COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_IP_OPT5') ?></li>
            <li><?php echo Language::_('COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_IP_OPT6') ?></li>
        </ol>

        <p>
			<?php echo Language::_('COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_YOURIP') ?>
            <code><?php echo $this->escape($this->myIP) ?></code>
        </p>

    </div>

	<form action="<?php echo ADMINTOOLSWP_URL; ?>&view=WhitelistedAddresses" method="post" class="akeeba-form--horizontal">
        <div class="akeeba-form-group">
            <label for="ip"><?php echo Language::_('COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_IP'); ?></label>
            <div>
                <input type="text" id="ip" name="ip" value="<?php echo isset($this->item) ? $this->escape($this->item->ip) : '' ?>" />
            </div>
        </div>

        <div class="akeeba-form-group">
            <label for="description"><?php echo Language::_('COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_DESCRIPTION'); ?></label>
            <div>
                <input type="text" id="description" name="description" value="<?php echo isset($this->item) ? $this->escape($this->item->description) : '' ?>" />
            </div>
        </div>

		<p class="submit">
			<input type="submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SAVE')?>"/>
			<a class="akeeba-btn--ghost" href="<?php echo ADMINTOOLSWP_URL; ?>&view=WhitelistedAddresses">
                <?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_CANCEL') ?>
            </a>
		</p>

        <input type="hidden" name="view" value="WhitelistedAddresses"/>
        <input type="hidden" name="task" value="save"/>
        <input type="hidden" name="id" value="<?php echo isset($this->item) ? $this->item->id : '' ?>" />
		<?php wp_nonce_field('postWhitelistedAddresses') ?>
	</form>
</section>
