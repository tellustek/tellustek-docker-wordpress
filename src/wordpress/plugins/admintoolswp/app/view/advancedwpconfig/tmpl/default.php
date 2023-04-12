<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;

/** @var $this Akeeba\AdminTools\Admin\View\AdvancedWPConfig\Html */

defined('ADMINTOOLSINC') or die;

$tabclass = $this->longConfig ? '' : 'akeeba-tabs';
?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=WPTools">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
        <?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
    <?php echo Language::_('COM_ADMINTOOLS_TITLE_ADVANCEDWPCONFIG');?>
</h1>

<div class="akeeba-block--warning">
    <h3><?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_WARNING')?></h3>

    <p><?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_WARNING_TEXT')?></p>
</div>

<section class="akeeba-panel">
    <form action="<?php echo ADMINTOOLSWP_URL; ?>&view=AdvancedWPConfig" method="post" class="akeeba-form--horizontal">
        <div id="advanced_configuration" class="<?php echo $tabclass?>">
	        <?php if ($this->longConfig):?>
                <h4><?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_POSTS'); ?></h4>
	        <?php else:?>
                <label for="posts" class="active">
			        <?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_POSTS'); ?>
                </label>
	        <?php endif;?>

            <section id="posts">
	            <?php include (__DIR__.'/posts.php'); ?>
            </section>

	        <?php if ($this->longConfig):?>
                <h4><?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_SITE'); ?></h4>
	        <?php else:?>
                <label for="site">
			        <?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_SITE'); ?>
                </label>
	        <?php endif;?>
            <section id="site">
		        <?php include (__DIR__.'/site.php'); ?>
            </section>

	        <?php if ($this->longConfig):?>
                <h4><?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_SYSTEM'); ?></h4>
	        <?php else:?>
                <label for="system">
			        <?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_SYSTEM'); ?>
                </label>
	        <?php endif;?>
            <section id="system">
		        <?php include (__DIR__.'/system.php'); ?>
            </section>

	        <?php if ($this->longConfig):?>
                <h4><?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_AUTOUPDATES'); ?></h4>
	        <?php else:?>
                <label for="autoupdates">
			        <?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_AUTOUPDATES'); ?>
                </label>
	        <?php endif;?>
            <section id="autoupdates">
		        <?php include (__DIR__.'/autoupdates.php'); ?>
            </section>
        </div>

        <p class="submit">
            <input type="submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_SAVE')?>"/>
            <input type="submit" class="akeeba-btn--orange" onclick="document.getElementById('task').value = 'apply'" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_ADVANCEDWPCONFIG_WRITE')?>"/>
            <a class="akeeba-btn--ghost" href="<?php echo ADMINTOOLSWP_URL; ?>&view=WPTools">
		        <?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_CANCEL') ?>
            </a>
        </p>

        <input type="hidden" name="view" value="AdvancedWPConfig"/>
        <input type="hidden" id="task" name="task" value="save"/>
		<?php wp_nonce_field('postAdvancedWPConfig') ?>
    </form>
</section>

<script>
	<?php if (!$this->longConfig): ?>
    jQuery(document).ready(function(){
        akeeba.fef.tabs();
    });
	<?php endif; ?>
</script>
