<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Awf\Text\Text;

defined('_AKEEBA') or die();

/** @var \Solo\View\S3import\Html $this */

$router = $this->getContainer()->router;

?>
<form name="adminForm" id="adminForm" action="@route('index.php?view=s3import')" method="POST"
      role="form">

    <div class="akeeba-hidden-fields-container">
        <input type="hidden" id="ak_s3import_folder" name="folder" value="<?php echo $this->root ?>" />
    </div>

    <div class="akeeba-panel--information">
        <div class="akeeba-form--inline">

            <div class="akeeba-form-group">
                <input type="text" size="40" name="s3access" id="s3access"
                       value="{{{ $this->s3access }}}"
                       placeholder="@lang('COM_AKEEBA_CONFIG_S3ACCESSKEY_TITLE')" />
            </div>
            <div class="akeeba-form-group">
                <input type="password" size="40" name="s3secret" id="s3secret"
                       value="{{{ $this->s3secret }}}"
                       placeholder="@lang('COM_AKEEBA_CONFIG_S3SECRETKEY_TITLE')" />
            </div>

	        @if(empty($this->buckets))
            <div class="akeeba-form-group">
                <button class="akeeba-btn--primary" type="submit" id="akeebaS3ImportResetRoot">
                    <span class="akion-wifi"></span>
			        @lang('COM_AKEEBA_S3IMPORT_LABEL_CONNECT')
                </button>
            </div>
	        @else
            <div class="akeeba-form-group">
		        {{ $this->bucketSelect }}
            </div>
            <div class="akeeba-form-group">
                <button class="akeeba-btn--primary" type="submit" id="akeebaS3ImportResetRoot">
                    <span class="akion-folder"></span>
			        @lang('COM_AKEEBA_S3IMPORT_LABEL_CHANGEBUCKET')
                </button>
            </div>
	        @endif
        </div>
	</div>

    <div class="akeeba-panel--information">
		<div id="ak_crumbs_container">
			<ul class="akeeba-breadcrumb">
				<li>
					<a data-s3prefix="{{ base64_encode('') }}" class="akeebaS3ImportChangeDirectory">
						@lang('SOLO_COMMON_LBL_ROOT')
					</a>
                    <span class="divider">/</span>
				</li>

				@if (!empty($this->crumbs))
					<?php $runningCrumb = ''; $i = 0; ?>
					@foreach($this->crumbs as $crumb)
						<?php $runningCrumb .= $crumb . '/'; $i++; ?>
                        <li>
                            <a
                                    class="akeebaS3ImportChangeDirectory"
                                    data-s3prefix="{{ base64_encode($runningCrumb) }}"
                            >
								{{{  $crumb }}}
                            </a>
	                        @if ($i < count($this->crumbs))
                                <span class="divider">/</span>
	                        @endif
                        </li>
					@endforeach
                @endif
			</ul>
		</div>
	</div>

    <div class="akeeba-container--50-50">
        <div>
            <div id="ak_folder_container" class="akeeba-panel--primary">
                <header class="akeeba-block-header">
                    <h3>
                        @lang('COM_AKEEBA_FILEFILTERS_LABEL_DIRS')
                    </h3>
                </header>

                <div id="folders">
                    @if(!empty($this->contents['folders']))
                        @foreach($this->contents['folders'] as $name => $record)
                        <div class="folder-container">
                            <span class="folder-icon-container">
                                <span class="akion-ios-folder"></span>
                            </span>
                            <span class="folder-name akeebaS3ImportChangeDirectory"
                                  data-s3prefix="{{ base64_encode($record['prefix']) }}"
                            >
                                {{{ rtrim($name, '/') }}}
                            </span>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div>
            <div id="ak_files_container" class="akeeba-panel--primary">
                <header class="akeeba-block-header">
                    <h3>
                        @lang('COM_AKEEBA_FILEFILTERS_LABEL_FILES')
                    </h3>
                </header>
                <div id="files">
                    @if(!empty($this->contents['files']))
                        @foreach($this->contents['files'] as $name => $record)
                        <div class="file-container">
                            <span class="file-icon-container">
                                <span class="akion-document"></span>
                            </span>
                            <span class="file-name file-clickable akeebaS3ImportObjectDownload"
                                  data-s3object="{{ base64_encode($name) }}">
                                {{{ basename($record['name']) }}}
                            </span>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</form>
