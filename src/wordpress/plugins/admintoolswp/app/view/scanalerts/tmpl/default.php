<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Html;
use Akeeba\AdminTools\Admin\Helper\Language;

/** @var $this \Akeeba\AdminTools\Admin\View\ScanAlerts\Html */

defined('ADMINTOOLSINC') or die;

$scan_id   = $this->input->getInt('scan_id', '');

/** @var \Akeeba\AdminTools\Admin\Model\Scans $scanModel */
$scanModel = $this->getModel('Scans');
$scanItem  = $scanModel->getItem($scan_id);

?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=Scans">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
	<?php echo Language::sprintf('COM_ADMINTOOLS_TITLE_SCANALERTS', $scan_id);?>
</h1>

<form id="admintoolswpForm" method="get" class="akeeba-form">
    <p style="float: left;margin: 0;">
        <em><?php echo nl2br($scanItem->comment);?></em>
    </p>

    <p class="search-box">
        <input type="search" name="path" value="<?php echo $this->escape($this->input->getString('path', null))?>"
               placeholder="<?php echo Language::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_URL')?>"/>
        <input type="submit" id="search-submit" class="akeeba-btn--primary" value="<?php echo Language::_('COM_ADMINTOOLS_LBL_COMMON_SEARCH')?>">
    </p>

    <div class="tablenav top">
        <?php echo Html::bulkActions(array(
                array('text' => 'marksafe', 'value' => 'publish'),
                array('text' => 'unmarksafe', 'value' => 'unpublish')
        ))?>

        <a class="akeeba-btn--red" href="<?php echo esc_url(ADMINTOOLSWP_URL.'&view=ScanAlerts&task=markallsafe&scan_id='.$scan_id)?>">
			<?php echo Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_MARKALLSAFE')?>
        </a>

        <a class="akeeba-btn--dark" href="<?php echo esc_url(ADMINTOOLSWP_URL.'&view=ScanAlerts&task=printlist&scan_id='.$scan_id)?>">
			<?php echo Language::_('COM_ADMINTOOLS_MSG_COMMON_PRINT')?>
        </a>

        <a class="akeeba-btn--dark" href="<?php echo esc_url(ADMINTOOLSWP_URL.'&view=ScanAlerts&task=export&scan_id='.$scan_id)?>">
			<?php echo Language::_('COM_ADMINTOOLS_MSG_COMMON_CSV')?>
        </a>

        <a class="akeeba-btn--primary" href="<?php echo ADMINTOOLSWP_URL; ?>&view=Scans&task=edit&id=<?php echo $scan_id?>">
			<?php echo Language::_('COM_ADMINTOOLS_SCANALERTS_EDIT_COMMENT')?>
        </a>

        <?php echo Html::pagination($this->total, $this->limitstart)?>
    </div>

    <table class="akeeba-table--striped">
        <thead>
        <tr>
            <td id="cb" class="manage-column column-cb check-column">
                <input id="cb-select-all-1" type="checkbox" />
            </td>
            <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_PATH'), 'path')?>
            <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS'), 'filestatus', 'width:150px')?>
            <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_THREAT_SCORE'), 'threat_score', 'width:120px')?>
            <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_ACKNOWLEDGED'), 'acknowledged', 'width:140px')?>
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
                        <input id="cb-select-<?php echo $item->admintools_scanalert_id ?>" type="checkbox" name="cid[]" value="<?php echo $item->admintools_scanalert_id?>" />
                    </td>
                    <td>
                        <?php
                        if (strlen($item->path) > 100)
                        {
                            $truncatedPath = true;
                            $path          = htmlspecialchars(substr($item->path, -100));
                            $alt           = 'title="' . htmlspecialchars($item->path) . '"';
                        }
                        else
                        {
                            $truncatedPath = false;
                            $path          = htmlspecialchars($item->path);
                            $alt           = '';
                        }

                        $html  = $truncatedPath ? "&hellip;" : '';
                        $html .= '<a href="'.ADMINTOOLSWP_URL.'&view=ScanAlerts&task=edit&id='.$item->admintools_scanalert_id.'" '.$alt.'>';
                        $html .= $path;
                        $html .= '</a>';
                        ?>
                        <?php echo $html ?>
                    </td>
                    <td>
                    <?php
                        $extra_class= '';

                        if(!$item->threat_score)
                        {
                            $extra_class = ' admintools-scanfile-nothreat';
                        }

                        if ($item->newfile)
                        {
                            $fstatus = 'new';
                        }
                        elseif ($item->suspicious)
                        {
                            $fstatus = 'suspicious';
                        }
                        else
                        {
                            $fstatus = 'modified';
                        }

                        $html = '<span class="admintools-scanfile-'.$fstatus.$extra_class.'">'.Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS_' . $fstatus).'</span>';
                    ?>
                        <?php echo $html ?>
                    </td>
                    <td>
                    <?php
                        if ($item->threat_score == 0)
                        {
                            $threatindex = 'none';
                        }
                        elseif ($item->threat_score < 10)
                        {
                            $threatindex = 'low';
                        }
                        elseif ($item->threat_score < 100)
                        {
                            $threatindex = 'medium';
                        }
                        else
                        {
                            $threatindex = 'high';
                        }

                        $html  = '<span class="admintools-scanfile-threat-'.$threatindex.'">';
                        $html .=    '<span class="admintools-scanfile-pic">&nbsp;</span>';
                        $html .=    $item->threat_score;
                        $html .= '</span>';
                    ?>
                        <?php echo $html ?>
                    </td>
                    <td>
                    <?php
                        $action = 'publish';
                        $text   = Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_MARKSAFE');
                        $class  = 'akeeba-label--red';
					    $icon = '<span class="akion-close"></span>';

                        if ($item->acknowledged)
						{
							$action = 'unpublish';
							$text   = Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_MARKUNSAFE');
							$class  = 'akeeba-label--green';
							$icon   = '<span class="akion-checkmark"></span>';
						}
                    ?>
                        <a class="<?php echo $class ?>" href="javascript:void(0)"
                           onclick="return atwpListItemTask('<?php echo 'cb-select-'.$item->admintools_scanalert_id ?>', '<?php echo $action ?>')">
                            <?php echo $icon ?>
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
            <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_PATH'), 'path')?>
            <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS'), 'filestatus')?>
            <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_THREAT_SCORE'), 'threat_score')?>
            <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_ACKNOWLEDGED'), 'acknowledged')?>
        </tr>
        </tfoot>
    </table>

    <input type="hidden" name="page" value="admintoolswp/admintoolswp.php" />
    <input type="hidden" name="view" value="ScanAlerts">
    <input type="hidden" name="scan_id" value="<?php echo $scan_id ?>">
	<?php wp_nonce_field('getScanAlerts', '_wpnonce', false) ?>
</form>
