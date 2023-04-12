<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Select;
use Akeeba\AdminTools\Library\Html\Select as SelectHelper;

defined('ADMINTOOLSINC') or die;
?>

<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
        <?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
    <?php echo Language::_('COM_ADMINTOOLS_TITLE_EXPORT_SETTINGS'); ?>
</h1>

<section class="akeeba-panel">
    <div id="emailtemplateWarning" class="akeeba-block--warning" style="display: none">
        <?php echo Language::_('COM_ADMINTOOLS_IMPORTANDEXPORT_EMAILTEMPLATE_WARN'); ?>
    </div>

    <form class="akeeba-form--horizontal" action="<?php echo ADMINTOOLSWP_URL; ?>&view=ImportAndExport" method="post" class="akeeba-form">
        <div class="akeeba-form-group">
            <label><?php echo Language::_('COM_ADMINTOOLS_IMPORTANDEXPORT_WAFCONFIG'); ?></label>
            <div>
				<?php echo SelectHelper::booleanswitch('exportdata[wafconfig]', 1); ?>
            </div>
        </div>
        <div class="akeeba-form-group">
            <label><?php echo Language::_('COM_ADMINTOOLS_IMPORTANDEXPORT_WAFEXCEPTIONS'); ?></label>
            <div>
				<?php echo SelectHelper::booleanswitch('exportdata[wafexceptions]', 1); ?>
            </div>
        </div>
	    <?php if (ADMINTOOLSWP_PRO): ?>
        <div class="akeeba-form-group">
            <label><?php echo Language::_('COM_ADMINTOOLS_IMPORTANDEXPORT_IPBLACKLIST'); ?></label>
            <div>
				<?php echo SelectHelper::booleanswitch('exportdata[ipblacklist]', 1); ?>
            </div>
        </div>
        <div class="akeeba-form-group">
            <label><?php echo Language::_('COM_ADMINTOOLS_IMPORTANDEXPORT_IPWHITELIST'); ?></label>
            <div>
				<?php echo SelectHelper::booleanswitch('exportdata[ipwhitelist]', 1); ?>
            </div>
        </div>
        <div class="akeeba-form-group">
            <label><?php echo Language::_('COM_ADMINTOOLS_IMPORTANDEXPORT_BADWORDS'); ?></label>
            <div>
				<?php echo SelectHelper::booleanswitch('exportdata[badwords]', 1); ?>
            </div>
        </div>
        <?php endif; ?>
        <div class="akeeba-form-group">
            <label><?php echo Language::_('COM_ADMINTOOLS_IMPORTANDEXPORT_EMAILTEMPLATES'); ?></label>
            <div>
				<?php echo SelectHelper::booleanswitch('exportdata[emailtemplates]', 0); ?>
            </div>
        </div>

        <p class="submit">
            <input type="submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_TITLE_EXPORT_SETTINGS')?>"/>
            <a href="<?php echo ADMINTOOLSWP_URL; ?>" class="akeeba-btn--ghost">
                <?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_CANCEL')?>
            </a>
        </p>

        <input type="hidden" name="view" value="ImportAndExport"/>
        <input type="hidden" name="task" value="doexport"/>
		<?php wp_nonce_field('postImportAndExport') ?>
    </form>
</section>
