<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Awf\Text\Text;

defined('_AKEEBA') or die();

/** @var   \Solo\View\Schedule\Html  $this */
?>

<div class="akeeba-tabs">
    <label for="absTabRunBackups" class="active">
		@lang('COM_AKEEBA_SCHEDULE_LBL_RUN_BACKUPS')
    </label>
    <section id="absTabRunBackups">
	    @include('Schedule/backup')
    </section>

    <label for="absTabCheckBackups">
		@lang('COM_AKEEBA_SCHEDULE_LBL_CHECK_BACKUPS')
    </label>
    <section id="absTabCheckBackups">
	    @include('Schedule/check')
    </section>
</div>

<p></p>
