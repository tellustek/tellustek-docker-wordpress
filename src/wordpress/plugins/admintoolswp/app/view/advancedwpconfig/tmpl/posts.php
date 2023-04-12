<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Select;
use Akeeba\AdminTools\Library\Html\Select as SelectHelper;

/** @var $this Akeeba\AdminTools\Admin\View\AdvancedWPConfig\Html */

defined('ADMINTOOLSINC') or die;

?>
<div class="akeeba-form-group">
	<label
		for="autosave_interval"
		rel="akeeba-sticky-tooltip"
		data-original-title="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_AUTOSAVE'); ?>"
		data-content="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_AUTOSAVE_TIP') ?>"
	>
		<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_AUTOSAVE') ?>
	</label>

	<div>
		<input type="text" name="autosave_interval" id="autosave_interval" class="akeeba-input-mini" value="<?php echo $this->wpconfig->autosave_interval?>"/>
	</div>
</div>

<div class="akeeba-form-group">
	<label
		rel="akeeba-sticky-tooltip"
		data-original-title="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_POSTREVISIONS'); ?>"
		data-content="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_POSTREVISIONS_TIP') ?>"
	>
		<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_POSTREVISIONS') ?>
	</label>

	<?php echo Select::post_revisions('post_revisions', $this->wpconfig->post_revisions)?>
</div>

<div class="akeeba-form-group">
	<label
		rel="akeeba-sticky-tooltip"
		data-original-title="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_EMPTYTRASH'); ?>"
		data-content="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_EMPTYTRASH_TIP') ?>"
	>
		<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_EMPTYTRASH') ?>
	</label>

	<div>
		<input type="text" name="empty_trash" class="akeeba-input-mini" value="<?php echo $this->wpconfig->empty_trash ?>"/>
	</div>
</div>

<div class="akeeba-form-group">
	<label
		rel="akeeba-sticky-tooltip"
		data-original-title="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_IMAGEEDITS'); ?>"
		data-content="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_IMAGEEDITS_TIP') ?>"
	>
		<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_IMAGEEDITS') ?>
	</label>

	<?php echo SelectHelper::booleanswitch('cleanup_image_edits', $this->wpconfig->cleanup_image_edits) ?>
</div>

<div class="akeeba-form-group">
    <label
            rel="akeeba-sticky-tooltip"
            data-original-title="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_IMAGESCALING'); ?>"
            data-content="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_IMAGESCALING_TIP') ?>"
    >
		<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_IMAGESCALING') ?>
    </label>

	<?php echo SelectHelper::booleanswitch('disable_image_scaling', $this->wafconfig['disable_image_scaling']) ?>
</div>
