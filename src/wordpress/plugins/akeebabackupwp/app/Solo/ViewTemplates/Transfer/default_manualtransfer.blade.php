<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_AKEEBA') or die();

/** @var  $this  Solo\View\Transfer\Html */

$dotPos    = strrpos($this->latestBackup['archivename'], '.');
$extension = substr($this->latestBackup['archivename'], $dotPos + 1);
$bareName  = basename($this->latestBackup['archivename'], '.' . $extension);

// Different video URL depending on whether we're inside Akeeba Backup for WordPress or Akeeba Solo
$videoCode = $this->getContainer()->segment->get('insideCMS', false) ? 'https://www.akeeba.com/videos/1215-akeeba-backup-wordpress/1651-abtw05-restoring-site-new-server.html' : 'https://www.akeeba.com/videos/1214-akeeba-solo/1637-abts05-restoring-site-new-server.html';

?>
<div id="akeeba-transfer-manualtransfer" class="akeeba-panel--primary" style="display: none;">
	<header class="akeeba-block-header">
        <h3>
	        @lang('COM_AKEEBA_TRANSFER_HEAD_MANUALTRANSFER')
        </h3>
	</header>

	<div class="akeeba-block--info">
		@lang('COM_AKEEBA_TRANSFER_LBL_MANUALTRANSFER_INFO')
	</div>

	<p>
		<a href="{{ $videoCode }}" class="akeeba-btn--primary--large" target="_blank">
			<span class="akion-play"></span>
			@lang('COM_AKEEBA_TRANSFER_LBL_MANUALTRANSFER_LINK')
		</a>
	</p>

	<h4>@lang('COM_AKEEBA_BUADMIN_LBL_BACKUPINFO')</h4>

    <h5>@lang('COM_AKEEBA_BUADMIN_LBL_ARCHIVENAME')</h5>

	<p>
		@if($this->latestBackup['multipart'] < 2)
			{{{ $this->latestBackup['archivename'] }}}
		@else
			@sprintf('COM_AKEEBA_TRANSFER_LBL_MANUALTRANSFER_MULTIPART', $this->latestBackup['multipart'])
		@endif
	</p>

	@if ($this->latestBackup['multipart'] >= 2)
    <ul>
        @for ($i = 1; $i < $this->latestBackup['multipart']; $i++)
            <li><{{{ $bareName . '.' . substr($extension, 0, 1) . sprintf('%02u', $i) }}}</li>
        @endfor
        <li>
            {{{ $this->latestBackup['archivename'] }}}
        </li>
    </ul>
    @endif

    <h5>
	    @lang('COM_AKEEBA_BUADMIN_LBL_ARCHIVEPATH')
    </h5>

    <p>
		{{{ \Solo\Helper\Utils::getRelativePath(APATH_BASE, dirname($this->latestBackup['absolute_path'])) }}}
	</p>
</div>
