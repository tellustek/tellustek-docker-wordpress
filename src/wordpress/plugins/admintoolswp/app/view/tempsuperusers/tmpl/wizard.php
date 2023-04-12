<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;

/** @var $this \Akeeba\AdminTools\Admin\View\TempSuperUsers\Html */

defined('ADMINTOOLSINC') or die;

?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=TempSuperUsers">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
	<?php echo Language::_('COM_ADMINTOOLS_TITLE_TEMPSUPERUSERS_ADD');?>
</h1>

<section class="akeeba-panel">
	<form action="<?php echo ADMINTOOLSWP_URL; ?>&view=TempSuperUsers" method="post" class="akeeba-form--horizontal">
        <section class="akeeba-panel--information">
            <header class="akeeba-block-header">
                <h3><?php echo Language::_('COM_ADMINTOOLS_TEMPSUPERUSERS_FIELD_EXPIRATION') ?></h3>
            </header>

            <div class="akeeba-form-group">
                <label for="expiration">
                    <?php echo Language::_('COM_ADMINTOOLS_TEMPSUPERUSERS_FIELD_EXPIRATION') ?>
                </label>

                <input type="date" id="expiration" name="expiration" value="<?php echo $this->userInfo['expiration'] ?>" />
            </div>
        </section>

        <section class="akeeba-panel--information">
            <header class="akeeba-block-header">
                <h3>
                    <?php echo Language::_('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_USERINFO') ?>
                </h3>
            </header>

            <div class="akeeba-form-group">
                <label for="username">
                    <?php echo Language::_('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_USERNAME') ?>
                </label>

                <input type="text" name="username" value="<?php echo $this->userInfo['username'] ?>" />
            </div>

            <div class="akeeba-form-group">
                <label for="password">
                    <?php echo Language::_('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_PASSWORD') ?>
                </label>

                <input type="text" name="password" value="<?php echo $this->userInfo['password'] ?>" />
            </div>

            <div class="akeeba-form-group">
                <label for="password2">
                    <?php echo Language::_('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_PASSWORD2') ?>
                </label>

                <input type="text" name="password2" value="<?php echo $this->userInfo['password2'] ?>" />
            </div>

            <div class="akeeba-form-group">
                <label for="email">
                    <?php echo Language::_('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_EMAIL') ?>
                </label>

                <input type="text" name="email" value="<?php echo $this->userInfo['email'] ?>" />
            </div>

            <div class="akeeba-form-group">
                <label for="name">
                    <?php echo Language::_('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_NAME') ?>
                </label>

                <input type="text" name="name" value="<?php echo $this->userInfo['name'] ?>" />
            </div>

            <div class="akeeba-form-group">
                <label for="role">
                    <?php echo Language::_('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_GROUPS') ?>
                </label>

                <select id="role" name="role">
		            <?php wp_dropdown_roles($this->userInfo['role']) ?>
                </select>
            </div>
        </section>

		<p class="submit">
			<input type="submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SAVE')?>"/>
			<a class="akeeba-btn--ghost" href="<?php echo ADMINTOOLSWP_URL; ?>&view=TempSuperUsers">
                <?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_CANCEL') ?>
            </a>
		</p>

        <input type="hidden" name="view" value="TempSuperUsers"/>
        <input type="hidden" name="task" value="save"/>
        <input type="hidden" name="id" value="<?php echo isset($this->item) ? $this->item->id : '' ?>" />
		<?php wp_nonce_field('postTempSuperUsers') ?>
	</form>
</section>
