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

// PLEASE NOTE: Those configuration values are inside the WAF configuration, since they will be applied by the MU-PLUGIN
// and it's way easier to find and retrieve them if they're stored in the WAF config instead of 
?>

<div class="akeeba-block--warning">
    <?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_AUTOUPDATES_WARNING'); ?>
</div>

<div class="akeeba-form-group">
    <label for="autoupdate_core">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_AUTOUPDATES_CORE'); ?>
    </label>

    <?php echo Select::core_autoupdates('core_autoupdates', $this->wafconfig['core_autoupdates']) ?>
</div>

<div class="akeeba-form-group">
    <label for="autoupdate_plugins">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_AUTOUPDATES_PLUGINS'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('autoupdate_plugins', $this->wafconfig['autoupdate_plugins']) ?>
</div>

<div class="akeeba-form-group">
    <label for="autoupdate_themes">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_AUTOUPDATES_THEMES'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('autoupdate_themes', $this->wafconfig['autoupdate_themes']) ?>
</div>

<div class="akeeba-form-group">
    <label for="autoupdate_translations">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_AUTOUPDATES_TRANSLATIONS'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('autoupdate_translations', $this->wafconfig['autoupdate_translations']) ?>
</div>