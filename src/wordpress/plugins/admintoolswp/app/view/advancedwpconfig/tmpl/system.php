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

<div class="akeeba-form-group">
	<label
		rel="akeeba-sticky-tooltip"
		data-original-title="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_DISABLE_EDIT'); ?>"
		data-content="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_DISABLE_EDIT_TIP') ?>"
	><?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_DISABLE_EDIT') ?></label>

	<?php echo SelectHelper::booleanswitch('disable_edit', $this->wpconfig->disable_edit) ?>
</div>

<div class="akeeba-form-group">
	<label
		rel="akeeba-sticky-tooltip"
		data-original-title="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_DEBUG'); ?>"
		data-content="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_DEBUG_TIP') ?>"
	><?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_DEBUG') ?></label>

	<?php echo SelectHelper::booleanswitch('debug', $this->wpconfig->debug) ?>
</div>

<div class="akeeba-form-group">
	<label
		rel="akeeba-sticky-tooltip"
		data-original-title="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_DEBUG_LOG'); ?>"
		data-content="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_DEBUG_LOG_TIP') ?>"
	><?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_DEBUG_LOG') ?></label>

	<?php echo SelectHelper::booleanswitch('debug_log', $this->wpconfig->debug_log) ?>
</div>

<div class="akeeba-form-group">
	<label
		rel="akeeba-sticky-tooltip"
		data-original-title="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_DEBUG_DISPLAY'); ?>"
		data-content="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_DEBUG_DISPLAY_TIP') ?>"
	><?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_DEBUG_DISPLAY') ?></label>

	<?php echo SelectHelper::booleanswitch('debug_display', $this->wpconfig->debug_display) ?>
</div>

<div class="akeeba-form-group">
	<label
		rel="akeeba-sticky-tooltip"
		data-original-title="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_ERROR_REPORTING'); ?>"
		data-content="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_ERROR_REPORTING_TIP') ?>"
	><?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_ERROR_REPORTING') ?></label>

	<?php echo Select::error_reporting('error_reporting', $this->wafconfig['error_reporting'])?>
</div>

<div class="akeeba-form-group">
	<label
		rel="akeeba-sticky-tooltip"
		data-original-title="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_JSCONCAT'); ?>"
		data-content="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_JSCONCAT_TIP') ?>"
	><?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_JSCONCAT') ?></label>

	<?php echo SelectHelper::booleanswitch('js_concat', $this->wpconfig->js_concat) ?>
</div>

<div class="akeeba-form-group">
	<label
		rel="akeeba-sticky-tooltip"
		data-original-title="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_MEMORY_LIMIT'); ?>"
		data-content="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_MEMORY_LIMIT_TIP') ?>"
	>
		<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_MEMORY_LIMIT') ?>
	</label>

	<div>
		<input type="text" name="memory_limit" class="akeeba-input-mini" value="<?php echo $this->wpconfig->memory_limit ?>"/>
	</div>
</div>

<div class="akeeba-form-group">
	<label
		rel="akeeba-sticky-tooltip"
		data-original-title="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_CACHE'); ?>"
		data-content="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_CACHE_TIP') ?>"
	><?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_CACHE') ?></label>

	<?php
	if (file_exists(WP_CONTENT_DIR.'/advanced-cache.php'))
	{
		echo SelectHelper::booleanswitch('cache', $this->wpconfig->cache);
	}
	else
	{
		?>
		<div class="akeeba-block--warning">
			<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_CACHE_NOFILE'); ?>
		</div>
		<?php
	}
	?>
</div>

<div class="akeeba-form-group">
	<label
		rel="akeeba-sticky-tooltip"
		data-original-title="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_ALTERNATECRON'); ?>"
		data-content="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_ALTERNATECRON_TIP') ?>"
	><?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_ALTERNATECRON') ?></label>

	<?php echo SelectHelper::booleanswitch('alternate_cron', $this->wpconfig->alternate_cron) ?>
</div>

<div class="akeeba-form-group">
	<label
		rel="akeeba-sticky-tooltip"
		data-original-title="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_DISABLECRON'); ?>"
		data-content="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_DISABLECRON_TIP') ?>"
	><?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_DISABLECRON') ?></label>

	<?php echo SelectHelper::booleanswitch('disable_cron', $this->wpconfig->disable_cron) ?>
</div>

<div class="akeeba-form-group">
	<label
		for="autosave_interval"
		rel="akeeba-sticky-tooltip"
		data-original-title="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_CRONTIMEOUT'); ?>"
		data-content="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_CRONTIMEOUT_TIP') ?>"
	><?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_CRONTIMEOUT') ?>
	</label>

	<div>
		<input type="text" name="cron_timeout" id="cron_timeout" class="akeeba-input-mini" value="<?php echo $this->wpconfig->cron_timeout?>"/>
	</div>
</div>
