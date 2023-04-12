<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Html;
use Akeeba\AdminTools\Admin\Helper\Language;
use Akeeba\AdminTools\Admin\Helper\Params as PluginParams;

/** @var \Akeeba\AdminTools\Admin\View\Scans\Html $this */

defined('ADMINTOOLSINC') or die;

$msg      = Language::_('COM_ADMINTOOLS_MSG_SCAN_LASTSERVERRESPONSE');
$urlStart = ADMINTOOLSWP_URL . '&view=Scans&task=startscan';
$urlStep  = ADMINTOOLSWP_URL . '&view=Scans&task=stepscan';
$root_url = ADMINTOOLSWP_URL;

$script = <<<JS

;// This comment is intentionally put here to prevent badly written plugins from causing a Javascript error
// due to missing trailing semicolon and/or newline in their code.
var admintools_scan_msg_ago = '$msg';
var admintools_scan_ajax_url_start='$urlStart';
var admintools_scan_ajax_url_step='$urlStep';
var admintools_root_url = '$root_url';

JS;

$params      = $params = PluginParams::getInstance();
$darkMode    = $params->getValue('darkmode', -1);
$classSuffix = ($darkMode == 1) ? '--dark' : '';
?>
<h1>
    <a class="akeeba-component-name" href="<?php echo ADMINTOOLSWP_URL; ?>&view=MalwareDetection">
        <span class="akion-chevron-left"></span>
        <span class="aklogo-admintools-wp-small"></span>
		<?php echo Language::_('COM_ADMINTOOLS') ?>
    </a>
	<?php echo Language::_('COM_ADMINTOOLS_TITLE_LOG');?>
</h1>

<form id="admintoolswpForm" method="get" class="akeeba-form">
    <input type="hidden" name="page" value="admintoolswp/admintoolswp.php" />
    <input type="hidden" name="view" value="Scans">
    <?php wp_nonce_field('getScans', '_wpnonce', false) ?>

    <div class="tablenav top">
        <?php echo Html::bulkActions(array('delete'))?>
        <button onclick="startScan();return false;" class="akeeba-btn--primary">
			<?php echo Language::_('COM_ADMINTOOLS_MSG_SCAN_SCANNOW')?>
        </button>

        <a class="akeeba-btn--red" href="<?php echo esc_url(ADMINTOOLSWP_URL.'&view=Scans&task=purge')?>">
			<?php echo Language::_('COM_ADMINTOOLS_MSG_SCAN_PURGE')?>
        </a>
        <?php echo Html::pagination($this->total, $this->limitstart)?>
    </div>

    <table class="akeeba-table--striped">
        <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <input id="cb-select-all-1" type="checkbox" />
                </td>
                <?php echo Html::tableHeader($this->input, '#', 'id')?>
                <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_SCAN_START'), 'scanstart')?>
                <td><?php echo Language::_('COM_ADMINTOOLS_LBL_SCAN_TOTAL')?></td>
                <td><?php echo Language::_('COM_ADMINTOOLS_LBL_SCAN_MODIFIED')?></td>
                <td><?php echo Language::_('COM_ADMINTOOLS_LBL_SCAN_THREATNONZERO')?></td>
                <td><?php echo Language::_('COM_ADMINTOOLS_LBL_SCAN_ADDED')?></td>
                <td><?php echo Language::_('COM_ADMINTOOLS_LBL_SCAN_ACTIONS')?></td>
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
                    <td><?php echo $item->id ?></td>
                    <td><?php echo $item->scanstart ?></td>
                    <td><?php echo $item->totalfiles ?></td>
                    <td>
                        <span class="admintools-files-<?php echo $item->files_modified ? 'alert' : 'noalert'?>">
                            <?php echo $item->files_modified ?>
                        </span>
                    </td>
                    <td>
                        <span class="admintools-files-<?php echo $item->files_suspicious ? 'alert' : 'noalert'?>">
                            <?php echo $item->files_suspicious ?>
                        </span>
                    </td>
                    <td>
                        <span class="admintools-files-<?php echo $item->files_new ? 'alert' : 'noalert'?>">
                            <?php echo $item->files_new ?>
                        </span>
                    </td>
                    <td>
                    <?php
                        if($item->files_modified + $item->files_new + $item->files_suspicious):
                    ?>
                        <a class="akeeba-btn--dark--small" href="<?php echo ADMINTOOLSWP_URL; ?>&view=ScanAlerts&scan_id=<?php echo $item->id?>">
                            <?php echo Language::_('COM_ADMINTOOLS_LBL_SCAN_ACTIONS_VIEW')?>
                        </a>
                    <?php
                        else:
                            echo Language::_('COM_ADMINTOOLS_LBL_SCAN_ACTIONS_NOREPORT');
                        endif;
                    ?>
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
                <?php echo Html::tableHeader($this->input, '#', 'id')?>
                <?php echo Html::tableHeader($this->input, Language::_('COM_ADMINTOOLS_LBL_SCAN_START'), 'scanstart')?>
                <td><?php echo Language::_('COM_ADMINTOOLS_LBL_SCAN_TOTAL')?></td>
                <td><?php echo Language::_('COM_ADMINTOOLS_LBL_SCAN_MODIFIED')?></td>
                <td><?php echo Language::_('COM_ADMINTOOLS_LBL_SCAN_THREATNONZERO')?></td>
                <td><?php echo Language::_('COM_ADMINTOOLS_LBL_SCAN_ADDED')?></td>
                <td><?php echo Language::_('COM_ADMINTOOLS_LBL_SCAN_ACTIONS')?></td>
            </tr>
        </tfoot>
    </table>
</form>


<div id="admintools-scan-dim" style="display: none">
	<div id="admintools-scan-container" class="akeeba-renderer-fef<?php echo $classSuffix ?>">
		<div>
            <div class="akeeba-block--info large">
                <h4>
		            <?php echo Language::_('COM_ADMINTOOLS_MSG_SCAN_PLEASEWAIT') ?>
                </h4>
                <p>
		            <?php echo Language::_('COM_ADMINTOOLS_MSG_SCAN_SCANINPROGRESS') ?>
                </p>
            </div>

            <p>
                <progress></progress>
            </p>

            <p>
                <span id="admintools-lastupdate-text" class="lastupdate"></span>
            </p>
		</div>
	</div>
</div>

<script type="application/javascript">
	<?php echo $script; ?>
</script>
