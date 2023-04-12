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
    <label for="tsrenable">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_TSRENABLE'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('tsrenable', $this->wafconfig['tsrenable']); ?>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_TSRENABLE_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="emailafteripautoban">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_EMAILAFTERIPAUTOBAN'); ?>
    </label>

	<input class="regular-text" type="text" size="50" name="emailafteripautoban"
		   value="<?php echo $this->escape($this->wafconfig['emailafteripautoban']); ?>"/>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_EMAILAFTERIPAUTOBAN_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="tsrstrikes">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_TSRSTRIKES'); ?>
    </label>
    
    <div>
        <input class="input-mini pull-left" type="text" size="5" name="tsrstrikes"
               value="<?php echo $this->escape($this->wafconfig['tsrstrikes']); ?>"/>
        <span class="floatme"><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_TSRNUMFREQ'); ?></span>
        <input class="input-mini" type="text" size="5" name="tsrnumfreq"
               value="<?php echo $this->escape($this->wafconfig['tsrnumfreq']); ?>"/>
        <?php echo Select::trsfreqlist('tsrfrequency', array('class' => 'input-small'), $this->wafconfig['tsrfrequency']); ?>
    </div>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_TSRSTRIKES_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="tsrbannum">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_TSRBANNUM'); ?>
    </label>

	<div>
		<input class="input-mini" type="text" size="5" name="tsrbannum"
			   value="<?php echo $this->escape($this->wafconfig['tsrbannum']); ?>"/>
		&nbsp;
		<?php echo Select::trsfreqlist('tsrbanfrequency', array(), $this->wafconfig['tsrbanfrequency']); ?>
	</div>

	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_TSRBANNUM_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="tsrpermaban">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_PERMABAN'); ?>
    </label>

	<?php echo SelectHelper::booleanswitch('permaban', $this->wafconfig['permaban']); ?>

	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_PERMABAN_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="permabannum">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_PERMABANNUM'); ?>
    </label>
    
    <div>
        <input class="input-mini" type="text" size="5" name="permabannum"
               value="<?php echo $this->escape($this->wafconfig['permabannum']); ?>"/>
        <span><?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_PERMABANNUM_2'); ?></span>
    </div>

	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_PERMABANNUM_TIP'); ?>
	</p>
</div>

<div class="akeeba-form-group">
    <label for="spammermessage">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_SPAMMERMESSAGE'); ?>
    </label>

	<input type="text" class="regular-text" name="spammermessage"
		   value="<?php echo htmlentities($this->wafconfig['spammermessage']) ?>"/>
	<p class="akeeba-help-text">
		<?php echo Language::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_LBL_SPAMMERMESSAGE_TIP'); ?>
	</p>
</div>
