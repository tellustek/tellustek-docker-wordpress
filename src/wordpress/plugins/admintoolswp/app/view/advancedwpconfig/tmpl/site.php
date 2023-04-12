<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;

defined('ADMINTOOLSINC') or die;

?>

<div class="akeeba-form-group">
	<label
		for="cookie_domain" rel="akeeba-sticky-tooltip"
		data-original-title="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_COOKIEDOMAIN'); ?>"
		data-content="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_COOKIEDOMAIN_TIP') ?>"
	>
		<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_COOKIEDOMAIN') ?>
	</label>

	<div>
		<input type="text" name="cookie_domain" id="cookie_domain" value="<?php echo $this->wpconfig->cookie_domain?>"/>
	</div>
</div>

<div class="akeeba-form-group">
	<label for="cookie_path">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_COOKIEPATH') ?>
	</label>

	<div>
		<input type="text" name="cookie_path" id="cookie_path" value="<?php echo $this->wpconfig->cookie_path?>"/>
	</div>
</div>

<div class="akeeba-form-group">
	<label for="sitecookie_path">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_SITECOOKIEPATH') ?>
	</label>

	<div>
		<input type="text" name="sitecookie_path" id="sitecookie_path" value="<?php echo $this->wpconfig->sitecookie_path?>"/>
	</div>
</div>

<div class="akeeba-form-group">
	<label for="admincookie_path">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_ADMINCOOKIEPATH') ?>
	</label>

	<div>
		<input type="text" name="admincookie_path" id="admincookie_path" value="<?php echo $this->wpconfig->admincookie_path?>"/>
	</div>
</div>

<div class="akeeba-form-group">
	<label for="plugincookie_path">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_PLUGINCOOKIEPATH') ?>
	</label>

	<div>
		<input type="text" name="plugincookie_path" id="plugincookie_path" value="<?php echo $this->wpconfig->plugincookie_path?>"/>
	</div>
</div>

<div class="akeeba-form-group">
	<label for="template_path">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_TEMPLATEPATH') ?>
	</label>

	<div>
		<input type="text" name="template_path" id="template_path" value="<?php echo $this->wpconfig->template_path?>"/>
	</div>
</div>

<div class="akeeba-form-group">
	<label for="stylesheet_path">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_STILESHEETPATH') ?>
	</label>

	<div>
		<input type="text" name="stylesheet_path" id="stylesheet_path" value="<?php echo $this->wpconfig->stylesheet_path?>"/>
	</div>
</div>