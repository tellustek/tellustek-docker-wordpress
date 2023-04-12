<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

/** @var $this \Akeeba\AdminTools\Admin\View\BlacklistedAddresses\Html */
use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Select;

defined('ADMINTOOLSINC') or die;
?>
<script type="application/javascript">
jQuery(document).ready(function($){
    $("#csvdelimiters").change(function(){
        var fieldDelimiter = $("#field_delimiter");
        var fieldEnclosure = $("#field_enclosure");

        if ($(this).val() == -99)
        {
            fieldDelimiter.show();
            fieldEnclosure.show();
        }
        else{
            fieldDelimiter.hide();
            fieldEnclosure.hide();
        }
    })
});
</script>

<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=BlacklistedAddresses">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
	<?php echo Language::_('COM_ADMINTOOLS_TITLE_IPBL').' - '.Language::_('COM_ADMINTOOLS_IMPORTANDEXPORT_DETAILS');?>
</h1>

    <form action="<?php echo ADMINTOOLSWP_URL; ?>&view=BlacklistedAddresses" method="post" name="adminForm" id="adminForm" class="akeeba-form--horizontal"
          enctype="multipart/form-data">
        <div class="akeeba-form-group">
            <label><?php echo Language::_('COM_ADMINTOOLS_IMPORTANDEXPORT_DELIMITERS'); ?></label>
            <div>
				<?php echo Select::csvdelimiters('csvdelimiters', 1, array('class'=>'minwidth')); ?>
                <p class="akeeba-help-text">
					<?php echo Language::_('COM_ADMINTOOLS_IMPORTANDEXPORT_DELIMITERS_DESC'); ?>
                </p>
            </div>
        </div>

        <div class="akeeba-form-group" id="field_delimiter" style="display:none">
            <label><?php echo Language::_('COM_ADMINTOOLS_IMPORTANDEXPORT_FIELD_DELIMITERS'); ?></label>
            <div>
                <input type="text" name="field_delimiter" value="">
                <p class="akeeba-help-text">
					<?php echo Language::_('COM_ADMINTOOLS_IMPORTANDEXPORT_FIELD_DELIMITERS_DESC'); ?>
                </p>
            </div>
        </div>

        <div class="akeeba-form-group" id="field_enclosure" style="display:none">
            <label><?php echo Language::_('COM_ADMINTOOLS_IMPORTANDEXPORT_FIELD_ENCLOSURE'); ?></label>
            <div>
                <input type="text" name="field_enclosure" value="">
                <p class="akeeba-help-text">
					<?php echo Language::_('COM_ADMINTOOLS_IMPORTANDEXPORT_FIELD_ENCLOSURE_DESC'); ?>
                </p>
            </div>
        </div>

        <div class="akeeba-form-group">
            <label><?php echo Language::_('COM_ADMINTOOLS_IMPORTANDEXPORT_FILE'); ?></label>
            <div>
                <input type="file" name="csvfile"/>
                <p class="akeeba-help-text">
					<?php echo Language::_('COM_ADMINTOOLS_IMPORTANDEXPORT_FILE_DESC'); ?>
                </p>
            </div>
        </div>

        <p class="submit">
            <input type="submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_BLACKLISTEDADDRESSES_IMPORT')?>"/>
            <a class="akeeba-btn--ghost" href="<?php echo ADMINTOOLSWP_URL; ?>&view=BlacklistedAddresses">
                <?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_CANCEL')?>
            </a>
        </p>

        <input type="hidden" name="view" value="BlacklistedAddresses"/>
        <input type="hidden" name="task" value="doimport"/>
		<?php wp_nonce_field('postBlacklistedAddresses')?>
    </form>
