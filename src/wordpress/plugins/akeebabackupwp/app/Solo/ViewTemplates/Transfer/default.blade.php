<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_AKEEBA') or die();

/** @var  $this  Solo\View\Transfer\Html */

?>
@if ($this->force)
    <div class="akeeba-block--warning">
        <h3>@lang('COM_AKEEBA_TRANSFER_FORCE_HEADER')</h3>
        <p>@lang('COM_AKEEBA_TRANSFER_FORCE_BODY')</p>
    </div>
@endif

@include('CommonTemplates/FTPBrowser')
@include('CommonTemplates/SFTPBrowser')
@include('Transfer/default_prerequisites')

@unless(empty($this->latestBackup))
    @include('Transfer/default_remoteconnection')
    @include('Transfer/default_manualtransfer')
    @include('Transfer/default_upload')
@endunless
