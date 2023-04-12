<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

/** @var Akeeba\AdminTools\Admin\View\ConfigureWAF\Html $this */

use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Select;
use Akeeba\AdminTools\Library\Html\Select as SelectHelper;

defined('ADMINTOOLSINC') or die;

?>
<div class="akeeba-form-group">
    <label
            for="leakedpwd"
            rel="akeeba-sticky-tooltip"
            data-original-title="<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LEAKEDPWD'); ?>"
            data-content="<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LEAKEDPWD_TIP'); ?>">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LEAKEDPWD'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('leakedpwd', $this->wafconfig['leakedpwd']); ?>
</div>

<div class="akeeba-form-group">
    <label
            for="leakedpwd"
            rel="akeeba-sticky-tooltip"
            data-original-title="<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LEAKEDPWD_GROUPS'); ?>"
            data-content="<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LEAKEDPWD_GROUPS_TIP'); ?>">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LEAKEDPWD_GROUPS'); ?>
    </label>

	<?php echo Select::roles('leakedpwd_roles[]', $this->wafconfig['leakedpwd_roles'], array('hideEmpty' => true, 'multiple' => true, 'size' => 10))?>

</div>

<div class="akeeba-form-group">
    <label class="control-label" for="loginerrormsg">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LOGINERROR'); ?>
    </label>

	<input type="text" size="20" id="loginerrormsg" name="loginerrormsg" value="<?php echo $this->escape($this->wafconfig['loginerrormsg']); ?>"/>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LOGINERROR_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label class="control-label" for="removerss">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_REMOVERSS'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('removerss', $this->wafconfig['removerss']); ?>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_REMOVERSS_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label class="control-label" for="removeblogclient">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_REMOVEBLOGCLIENT'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('removeblogclient', $this->wafconfig['removeblogclient']); ?>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_REMOVEBLOGCLIENT_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label class="control-label" for="sessionnumduration">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SESSIONDURATION'); ?>
    </label>

    <div>
        <input type="text" size="5" id="sessionnumduration" name="sessionnumduration" value="<?php echo $this->escape($this->wafconfig['sessionnumduration']); ?>"/>
        <?php echo Select::freqlist('sessionduration', array(), $this->wafconfig['sessionduration']); ?>
		<span class="akeeba-label--grey">
        	<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SESSIONDURATION_REGULAR') ?>
		</span>
	</div>
	<div>
		<input type="text" size="5" name="sessionnumduration_remember" value="<?php echo $this->escape($this->wafconfig['sessionnumduration_remember']); ?>"/>
        <?php echo Select::freqlist('sessionduration_remember', array(), $this->wafconfig['sessionduration_remember']); ?>
		<span class="akeeba-label--grey">
        	<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SESSIONDURATION_REMEMBER') ?>
		</span>
    </div>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SESSIONDURATION_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label class="control-label" for="nonewadmins">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_NONEWADMINS'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('nonewadmins', $this->wafconfig['nonewadmins']); ?>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_NONEWADMINS_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="trackfailedlogins">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_TRACKFAILEDLOGINS'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('trackfailedlogins', $this->wafconfig['trackfailedlogins']); ?>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_TRACKFAILEDLOGINS_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="logusernames">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LOGUSERNAMES'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('logusernames', $this->wafconfig['logusernames']); ?>
    <p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LOGUSERNAMES_TIP'); ?>
    </p>
</div>

<div class="akeeba-form-group">
    <label for="disablexmlrpc">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEXMLRPC'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('disablexmlrpc', $this->wafconfig['disablexmlrpc']); ?>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEXMLRPC_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="filteremailregistration">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_FILTER_REGISTRATION'); ?>
    </label>

	<?php
	$checked_1 = $this->wafconfig['filteremailregistration'] == 'allow' ? '' : 'checked ';
	$checked_2 = $this->wafconfig['filteremailregistration'] == 'block' ? 'checked ' : '';
	?>

    <div class="akeeba-toggle">
        <input type="radio" class="radio-allow" name="filteremailregistration" <?php echo $checked_2 ?> id="filteremailregistration-2" value="allow">
        <label for="filteremailregistration-2" class="green"><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_FILTER_REGISTRATION_ALLOW') ?></label>
        <input type="radio" class="radio-block" name="filteremailregistration" <?php echo $checked_1 ?> id="filteremailregistration-1" value="block">
        <label for="filteremailregistration-1" class="red"><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_FILTER_REGISTRATION_BLOCK') ?></label>
    </div>

    <p class="akeeba-help-text">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_FILTER_REGISTRATION_TIP'); ?>
    </p>
</div>

<div class="akeeba-form-group">
    <label for="blockedemaildomains">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_BLOCKEDEMAILDOMAINS'); ?>
    </label>

	<textarea id="blockedemaildomains" name="blockedemaildomains" cols="50" rows="5"><?php echo $this->escape($this->wafconfig['blockedemaildomains']); ?></textarea>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_BLOCKEDEMAILDOMAINS_TIP'); ?>
	</p>
</div>
