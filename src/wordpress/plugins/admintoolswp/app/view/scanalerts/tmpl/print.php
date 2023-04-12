<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

use Akeeba\AdminTools\Admin\Helper\Language;

defined('ADMINTOOLSINC') or die;

$scan_id    = $this->input->getInt('scan_id', 0);
$jDate      = new DateTime($this->scan->scanstart, new DateTimeZone('UTC'));

?>
<html>
    <head>
        <link href="<?php echo ADMINTOOLSWP_MEDIAURL.'/app/media/css/backend.min.css'?>" rel="stylesheet">
    </head>
    <body onload="print()">
        <h1>
            <?php echo Language::sprintf('COM_ADMINTOOLS_TITLE_SCANALERTS', $scan_id) ?>
        </h1>
        <h2>
            <?php echo $jDate->format('l, d F Y H:i') ?>
        </h2>

        <table class="table">
            <thead>
            <tr>
                <th width="20"></th>
                <th>
                    <?php echo Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_PATH') ?>
                </th>
                <th width="80">
                    <?php echo Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS') ?>
                </th>
                <th width="40">
                    <?php echo Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_THREAT_SCORE') ?>
                </th>
                <th width="40">
                    <?php echo Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_ACKNOWLEDGED') ?>
                </th>
            </tr>
            </thead>
            <tbody>
            <?php if ($count = count($this->items)): ?>
                <?php
                $i = 0;
                $m = 1;
                foreach ($this->items as $item):
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

                    if (strlen($item->path) > 100)
                    {
                        $truncatedPath = true;
                        $path = $this->escape(substr($item->path, -100));
                        $alt = 'title="' . $this->escape($item->path) . '"';
                    }
                    else
                    {
                        $truncatedPath = false;
                        $path = $this->escape($item->path);
                        $alt = '';
                    }
                    ?>
                    <tr class="row<?php $m = 1 - $m;
                    echo $m; ?>">
                        <td><?php echo $i + 1 ?></td>
                        <td>
                            <?php echo $truncatedPath ? "&hellip;" : ''; ?>
                            <a href="<?php echo ADMINTOOLSWP_URL; ?>&view=ScanAlerts&task=edit&id=<?php echo $item->admintools_scanalert_id ?>" <?php echo $alt ?>>
                                <?php echo $path ?>
                            </a>
                        </td>
                        <td class="admintools-scanfile-<?php echo $fstatus ?> <?php if (!$item->threat_score): ?>admintools-scanfile-nothreat<?php endif ?>">
                            <?php echo Language::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS_' . $fstatus) ?>
                        </td>
                        <td class="admintools-scanfile-threat-<?php echo $threatindex ?>">
                            <?php echo $item->threat_score ?>
                        </td>
                        <td>
                            <?php if ($item->acknowledged): ?>
                                <span class="admintools-scanfile-markedsafe">
                        <?php echo Language::_('JYES') ?>
                        </span>
                            <?php else: ?>
                                <?php echo Language::_('JNO') ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                    $i++;
                endforeach;
                ?>
            <?php else: ?>
                <tr>
                    <td colspan="20" align="center"><?php echo Language::_('COM_ADMINTOOLS_MSG_COMMON_NOITEMS') ?></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </body>
</html>
