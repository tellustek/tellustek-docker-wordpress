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
    @lang('COM_AKEEBA_SCHEDULE_LBL_CHECK_BACKUPS')
</h2>

<p>
    @lang('COM_AKEEBA_SCHEDULE_LBL_HEADERINFO')
</p>

{{-- CLI CRON jobs --}}
@include('Schedule/check_cli')

{{-- Alternate CLI CRON jobs (using legacy front-end) --}}
@include('Schedule/check_altcli')

{{-- Frontend backup --}}
@include('Schedule/check_frontend')
