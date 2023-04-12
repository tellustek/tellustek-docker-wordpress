<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Html;
use Akeeba\AdminTools\Admin\Helper\Language;

/** @var $this \Akeeba\AdminTools\Admin\View\BlacklistedAddresses\Html */

defined('ADMINTOOLSINC') or die;

?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=WebApplicationFirewall">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
	<?php echo Language::_('COM_ADMINTOOLS_TITLE_IPBL');?>
</h1>

<?php if (!$this->blacklist_enabled):?>
<div class="akeeba-block--warning">
    <h3>
        <?php echo Language::_('COM_ADMINTOOLS_BLACKLISTEDADDRESSES_ERR_NOTENABLED_TITLE'); ?>
    </h3>
    <p>
        <?php echo Language::_('COM_ADMINTOOLS_BLACKLISTEDADDRESSES_ERR_NOTENABLED_BODY'); ?>
    </p>
</div>
<?php endif; ?>

<?php if ($this->toomanyips):?>
<div class="akeeba-block--failure">
    <h3>
        <?php echo Language::_('COM_ADMINTOOLS_BLACKLISTEDADDRESSES_ERR_TOOMANY_TITLE'); ?>
    </h3>
    <p>
        <?php echo Language::sprintf('COM_ADMINTOOLS_BLACKLISTEDADDRESSES_ERR_TOOMANY_BODY', 'https://www.akeeba.com/documentation/admin-tools/waf-ip-blacklist.html#do-not-overdo-it-with-ip-blacklisting'); ?>
    </p>
</div>
<?php endif; ?>

<form method="get" class="akeeba-form">
    <input type="hidden" name="page" value="admintoolswp/admintoolswp.php" />
    <input type="hidden" name="view" value="BlacklistedAddresses" />
    <?php wp_nonce_field('getBlacklistedAddresses', '_wpnonce', false) ?>

    <p class="search-box">
        <input type="search" name="ip" value="<?php echo $this->escape($this->input->getString('ip', null))?>"
               placeholder="<?php echo Language::_('COM_ADMINTOOLS_LBL_BLACKLISTEDADDRESS_IP')?>"/>
        <input type="search" name="description" value="<?php echo $this->escape($this->input->getString('description', null))?>"
               placeholder="<?php echo Language::_('COM_ADMINTOOLS_LBL_BLACKLISTEDADDRESS_DESCRIPTION')?>"/>

        <input type="submit" id="search-submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SEARCH')?>">
    </p>

    <div class="tablenav top">
        <?php echo Html::bulkActions(array('delete'))?>

        <a class="akeeba-btn--dark" href="<?php echo esc_url(ADMINTOOLSWP_URL.'&view=BlacklistedAddresses&task=import')?>">
			<?php echo Language::_('COM_ADMINTOOLS_BLACKLISTEDADDRESSES_IMPORT')?>
        </a>

        <a class="akeeba-btn--dark" href="<?php echo esc_url(ADMINTOOLSWP_URL.'&view=BlacklistedAddresses&task=export')?>">
			<?php echo Language::_('COM_ADMINTOOLS_BLACKLISTEDADDRESSES_EXPORT')?>
        </a>

        <a class="akeeba-btn--green" href="<?php echo esc_url(ADMINTOOLSWP_URL.'&view=BlacklistedAddresses&task=add')?>">
			<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_ADD')?>
        </a>

        <?php echo Html::pagination($this->total, $this->limitstart)?>
    </div>

    <table class="akeeba-table--striped">
        <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column" style="width:40px;">
                    <input id="cb-select-all-1" type="checkbox" />
                </td>
                <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_BLACKLISTEDADDRESS_IP'), 'ip')?>
                <td><?php echo Language::_('COM_ADMINTOOLS_LBL_BLACKLISTEDADDRESS_DESCRIPTION')?></td>
            </tr>
        </thead>

        <tbody>
        <?php if (!$this->items):?>
            <tr>
                <td colspan="20"><?php echo Language::_('COM_ADMINTOOLS_MSG_COMMON_NOITEMS')?></td>
            </tr>
        <?php else: ?>
            <?php
                foreach($this->items as $item):
                    $link = ADMINTOOLSWP_URL.'&view=BlacklistedAddresses&task=edit&id='.$item->id;
            ?>
                <tr>
                    <td class="check-column">
                        <input id="cb-select-<?php echo $item->id ?>" type="checkbox" name="cid[]" value="<?php echo $item->id?>" />
                    </td>
                    <td>
                        <a href="<?php echo $link; ?>">
                            <?php echo $item->ip ?>
                        </a>
                    </td>
                    <td><?php echo $item->description ?></td>
                </tr>
            <?php endforeach;?>
        <?php endif;?>
        </tbody>

        <tfoot>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <input id="cb-select-all-2" type="checkbox" />
                </td>
                <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_BLACKLISTEDADDRESS_IP'), 'ip')?>
                <td><?php echo Language::_('COM_ADMINTOOLS_LBL_BLACKLISTEDADDRESS_DESCRIPTION')?></td>
            </tr>
        </tfoot>
    </table>
</form>
