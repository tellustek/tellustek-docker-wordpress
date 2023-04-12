<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;

defined('ADMINTOOLSINC') or die;
?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
	<?php echo Language::_('COM_ADMINTOOLS_TITLE_IMPORT_SETTINGS'); ?>
</h1>

<section class="akeeba-panel">
    <form class="akeeba-form--horizontal" action="<?php echo ADMINTOOLSWP_URL; ?>&view=ImportAndExport" method="post" class="akeeba-form" enctype="multipart/form-data">
        <div class="akeeba-form-group">
            <label><?php echo Language::_('COM_ADMINTOOLS_IMPORTANDEXPORT_FILE'); ?></label>
            <div>
                <input type="file" name="importfile" value="" />
            </div>
        </div>

        <p class="submit">
            <input type="submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_TITLE_IMPORT_SETTINGS')?>"/>
            <a class="akeeba-btn--ghost" href="<?php echo ADMINTOOLSWP_URL; ?>">
                <?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_CANCEL')?>
            </a>
        </p>

        <input type="hidden" name="view" value="ImportAndExport"/>
        <input type="hidden" name="task" value="doimport"/>
		<?php wp_nonce_field('postImportAndExport') ?>
    </form>
</section>
