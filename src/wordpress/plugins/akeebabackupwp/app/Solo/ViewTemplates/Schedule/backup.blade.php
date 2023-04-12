<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var \Solo\View\Schedule\Html $this */

// Protect from unauthorized access
defined('_AKEEBA') or die();
?>
<h2>
    @lang('COM_AKEEBA_SCHEDULE_LBL_RUN_BACKUPS')
</h2>

<p>
    @lang('COM_AKEEBA_SCHEDULE_LBL_HEADERINFO')
</p>

{{-- CLI CRON jobs --}}
@include('Schedule/backup_cli')

{{-- Alternate CLI CRON jobs (using legacy front-end) --}}
@include('Schedule/backup_altcli')

{{-- Frontend backup --}}
@include('Schedule/backup_frontend')

{{-- JSON API --}}
@include('Schedule/backup_json')
