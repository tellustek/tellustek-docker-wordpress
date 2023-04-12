<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;

/** @var $this \Akeeba\AdminTools\Admin\View\SchedulingInformation\Html */

// Protect from unauthorized access
defined('ADMINTOOLSINC') or die();

?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=MalwareDetection">
        <span class="akion-chevron-left"></span><span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS').'</a>'.Language::_('COM_ADMINTOOLS_TITLE_SCHEDULINGINFORMATION'); ?>
</h1>

<h3>
    <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_RUN_FILESCANNER'); ?>
</h3>

<section class="akeeba-panel">
    <p>
        <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_HEADERINFO'); ?>
    </p>

    <h4><?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_CLICRON'); ?></h4>

    <p class="akeeba-block--info">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_CLICRON_INFO_WP'); ?>
        <br/>
        <a class="akeeba-btn--small" href="https://www.akeeba.com/documentation/admin-tools/php-file-scanner-cron.html" target="_blank">
            <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_GENERICREADDOC'); ?>
        </a>
    </p>
    <p>
        <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_GENERICUSECLI'); ?>
        <code>
            <?php echo $this->escape($this->croninfo->info->php_path); ?>

            <?php echo $this->escape($this->croninfo->cli->path); ?>

        </code>
    </p>
    <p>
        <span class="akeeba-label--red"><?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_CLIGENERICIMPROTANTINFO'); ?></span>
        <?php echo Language::sprintf('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_CLIGENERICINFO', $this->croninfo->info->php_path); ?>

    </p>

    <h4><?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTENDBACKUP'); ?></h4>

    <p class="akeeba-block--info">
        <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTENDBACKUP_INFO'); ?>
        <br/>
        <a class="akeeba-btn--small" href="https://www.akeeba.com/documentation/admin-tools/php-file-scanner-frontend.html" target="_blank">
            <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_GENERICREADDOC'); ?>
        </a>
    </p>
    <?php if (!$this->croninfo->info->feenabled): ?>
        <p class="akeeba-block--failure">
            <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_DISABLED_WP'); ?>
        </p>
    <?php elseif (!trim($this->croninfo->info->secret)): ?>
        <p class="akeeba-block--failure">
            <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_SECRET_WP'); ?>
        </p>
    <?php else: ?>
        <p>
            <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTENDBACKUP_MANYMETHODS'); ?>
        </p>

        <div class="akeeba-tabs">
            <label for="webcron" class="active">
                <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTENDBACKUP_TAB_WEBCRON'); ?>
            </label>

            <section id="webcron">
                <p>
                    <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON'); ?><br>
					<?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_INFO'); ?>
                </p>

                <table class="akeeba-table--striped">
                    <tr>
                        <td width="20%">
                            <strong><?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_NAME'); ?></strong>
                        </td>
                        <td>
                            <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_NAME_INFO'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong><?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_TIMEOUT'); ?></strong>
                        </td>
                        <td>
                            <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_TIMEOUT_INFO'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong><?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_URL'); ?></strong>
                        </td>
                        <td>
                            <?php echo $this->escape($this->croninfo->info->root_url.'/'.$this->croninfo->frontend->path); ?>

                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong><?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_LOGIN'); ?></strong>
                        </td>
                        <td>
                            <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_LOGINPASSWORD_INFO'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong><?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_PASSWORD'); ?></strong>
                        </td>
                        <td>
                            <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_LOGINPASSWORD_INFO'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong><?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_EXECUTIONTIME'); ?></strong>
                        </td>
                        <td>
                            <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_EXECUTIONTIME_INFO'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong><?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_ALERTS'); ?></strong>
                        </td>
                        <td>
                            <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_ALERTS_INFO'); ?>
                        </td>
                    </tr>
                </table>

                <p>
					<?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_THENCLICKSUBMIT'); ?>
                </p>
            </section>

            <label for="wget">
                <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTENDBACKUP_TAB_WGET'); ?>
            </label>

            <section id="wget">
                <p>
                    <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WGET'); ?><br />
                    <code>
                        wget --max-redirect=10000 "<?php echo $this->escape($this->croninfo->info->root_url.'/'.$this->croninfo->frontend->path); ?>" -O - 1>/dev/null 2>/dev/null
                    </code>
                </p>
            </section>

            <label for="curl">
                <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTENDBACKUP_TAB_CURL'); ?>
            </label>

            <section id="curl">
                <p>
                    <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_CURL'); ?><br />
                    <code>
                        curl -L --max-redirs 1000 -v "<?php echo $this->escape($this->croninfo->info->root_url.'/'.$this->croninfo->frontend->path); ?>" 1>/dev/null 2>/dev/null
                    </code>
                </p>
            </section>

            <label for="script">
                <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTENDBACKUP_TAB_SCRIPT'); ?>
            </label>

            <section id="script">
                <p>
                    <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_CUSTOMSCRIPT'); ?>
                <pre>
    <?php echo '&lt;?php'; ?>


                        $curl_handle=curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, '<?php echo $this->escape($this->croninfo->info->root_url.'/'.$this->croninfo->frontend->path); ?>');
        curl_setopt($curl_handle,CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curl_handle,CURLOPT_MAXREDIRS, 10000);
        curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER, 1);
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);
        if (empty($buffer))
            echo "Sorry, the scan didn't work.";
        else
            echo $buffer;
						<?php echo '?&gt;'; ?>

                    </pre>
                </p>
            </section>

            <label for="url">
                <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTENDBACKUP_TAB_URL'); ?>
            </label>

            <section id="url">
                <p>
                    <?php echo Language::_('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_RAWURL'); ?><br />
                    <code>
                        <?php echo $this->escape($this->croninfo->info->root_url); ?>/<?php echo $this->escape($this->croninfo->frontend->path); ?>

                    </code>
                </p>
            </section>
        </div>

    <?php endif; ?>
</section>