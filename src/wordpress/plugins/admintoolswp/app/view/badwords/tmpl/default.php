<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Html;
use Akeeba\AdminTools\Admin\Helper\Language;

/** @var $this \Akeeba\AdminTools\Admin\View\BadWords\Html */

defined('ADMINTOOLSINC') or die;

?>

<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=WebApplicationFirewall">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
	<?php echo Language::_('COM_ADMINTOOLS_TITLE_BADWORDS');?>
</h1>

<form method="get" class="akeeba-form">
    <input type="hidden" name="page" value="admintoolswp/admintoolswp.php" />
    <input type="hidden" name="view" value="BadWords" />
    <?php wp_nonce_field('getBadWords', '_wpnonce', false) ?>

    <p class="search-box">
        <input type="search" name="word" value="<?php echo $this->escape($this->input->getString('word', null))?>"
               placeholder="<?php echo Language::_('COM_ADMINTOOLS_LBL_BADWORD_WORD')?>"/>
        <input type="submit" id="search-submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SEARCH')?>">
    </p>

    <div class="tablenav top">
        <?php echo Html::bulkActions(array('delete'))?>
        <a class="akeeba-btn--green" href="<?php echo esc_url(ADMINTOOLSWP_URL.'&view=BadWords&task=add')?>">
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
                <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_BADWORD_WORD'), 'word')?>
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
                    $link = ADMINTOOLSWP_URL.'&view=BadWords&task=edit&id='.$item->id;
            ?>
                <tr>
                    <td class="check-column">
                        <input id="cb-select-<?php echo $item->id ?>" type="checkbox" name="cid[]" value="<?php echo $item->id?>" />
                    </td>
                    <td>
                        <a href="<?php echo $link; ?>">
                            <?php echo $item->word ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach;?>
        <?php endif;?>
        </tbody>

        <tfoot>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <input id="cb-select-all-2" type="checkbox" />
                </td>
                <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_BADWORD_WORD'), 'word')?>
            </tr>
        </tfoot>
    </table>
</form>
