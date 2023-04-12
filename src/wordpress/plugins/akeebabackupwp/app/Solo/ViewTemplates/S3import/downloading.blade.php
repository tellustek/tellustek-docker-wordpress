<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Awf\Text\Text;

defined('_AKEEBA') or die();

/** @var \Solo\View\S3import\Html $this */

?>
@inlineCss('dl { display: none; }')

<div id="backup-percentage" class="akeeba-progress">
    <div id="progressbar-inner" class="akeeba-progress-fill" style="width: {{ (int) $this->percent }}%"></div>
    <div class="akeeba-progress-status">
		{{ (int) $this->percent }}%
    </div>
</div>

<div class="akeeba-panel--information">
    <p>
        @sprintf('COM_AKEEBA_REMOTEFILES_LBL_DOWNLOADEDSOFAR', $this->done, $this->total, $this->percent)
    </p>
</div>
