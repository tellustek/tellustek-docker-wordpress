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
<div class="akeeba-panel--information">
    <header class="akeeba-block-header">
        <h3>@lang('COM_AKEEBA_SCHEDULE_LBL_CLICRON')</h3>
    </header>

    <div class="akeeba-block--info">
        <p>
            @lang('COM_AKEEBA_SCHEDULE_LBL_CLICRON_INFO')
        </p>
        <p>
            <a class="akeeba-btn--teal"
               href="https://www.akeeba.com/documentation/akeeba-solo/native-cron-script.htm"
               target="_blank">
                <span class="akion-ios-book"></span>
                @lang('COM_AKEEBA_SCHEDULE_LBL_GENERICREADDOC')
            </a>
        </p>
    </div>

    <p>
        @lang('COM_AKEEBA_SCHEDULE_LBL_GENERICUSECLI')
        <code>
            {{{ $this->escape($this->croninfo->info->php_path) }}}
            {{{ $this->escape($this->croninfo->cli->path) }}}
        </code>
    </p>
    <p>
        <span class="akeeba-label--warning">@lang('COM_AKEEBA_SCHEDULE_LBL_CLIGENERICIMPROTANTINFO')</span>
        @sprintf('COM_AKEEBA_SCHEDULE_LBL_CLIGENERICINFO', $this->croninfo->info->php_path)
    </p>

</div>