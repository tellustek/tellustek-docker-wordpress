<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Html;
use Akeeba\AdminTools\Admin\Helper\Language;

defined('ADMINTOOLSINC') or die;
?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=WebApplicationFirewall">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
	<?php echo Language::_('COM_ADMINTOOLS_TITLE_IPAUTOBANHISTORY');?>
</h1>

<form id="admintoolswpForm" method="get" class="akeeba-form">
    <input type="hidden" name="page" value="admintoolswp/admintoolswp.php" />
    <input type="hidden" name="view" value="IPAutoBanHistories" />
    <?php wp_nonce_field('getIPAutoBanHistories', '_wpnonce', false) ?>

    <p class="search-box">
        <input type="search" name="ip" placeholder="<?php echo Language::_('COM_ADMINTOOLS_LBL_AUTOBANNEDADDRESS_IP')?>"/>
        <input type="submit" id="search-submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SEARCH')?>">
    </p>

    <div class="tablenav top">
        <?php echo Html::bulkActions(array('delete'))?>
        <?php echo Html::pagination($this->total, $this->limitstart)?>
    </div>

    <table class="akeeba-table--striped">
        <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <input id="cb-select-all-1" type="checkbox" />
                </td>
                <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_AUTOBANNEDADDRESS_IP'), 'ip')?>
                <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_AUTOBANNEDADDRESS_REASON'), 'reason')?>
                <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_AUTOBANNEDADDRESS_UNTIL'), 'until')?>
            </tr>
        </thead>

        <tbody>
            <?php if (!$this->items):?>
                <tr>
                    <td colspan="20"><?php echo Language::_('COM_ADMINTOOLS_MSG_COMMON_NOITEMS')?></td>
                </tr>
            <?php else: ?>
            <?php foreach($this->items as $item):?>
                <tr>
                    <td class="check-column">
                        <input id="cb-select-<?php echo $item->id ?>" type="checkbox" name="cid[]" value="<?php echo $item->id?>" />
                    </td>
                    <td><?php echo $item->ip ?></td>
                    <td><?php echo Language::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON_'.$item->reason) ?></td>
                    <td><?php echo $item->until ?></td>
                </tr>
            <?php endforeach;?>
            <?php endif;?>
        </tbody>

        <tfoot>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <input id="cb-select-all-2" type="checkbox" />
                </td>
                <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_AUTOBANNEDADDRESS_IP'), 'ip')?>
                <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_AUTOBANNEDADDRESS_REASON'), 'reason')?>
                <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_AUTOBANNEDADDRESS_UNTIL'), 'until')?>
            </tr>
        </tfoot>
    </table>
</form>
