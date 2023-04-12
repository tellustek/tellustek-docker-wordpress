<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Awf\Text\Text;

defined('_AKEEBA') or die();

/** @var \Solo\View\Discover\Html $this */

$hasFiles = !empty($this->files);
$task = $hasFiles ? 'import' : 'main';
?>
<form name="adminForm" id="adminForm" action="@route('index.php?view=discover&task=' . $task)" method="POST" class="akeeba-form--horizontal--with-hidden" role="form">
	@if($hasFiles)
	<div class="akeeba-panel--information akeeba-form--horizontal">
        <div class="akeeba-form-group">
            <label for="directory2">@lang('COM_AKEEBA_DISCOVER_LABEL_DIRECTORY')</label>
            <input type="text" name="directory2" id="directory2" value="{{{ $this->directory }}}" disabled="disabled" size="70" />
        </div>
    </div>

	<div class="akeeba-form-group">
		<label for="files">
			@lang('COM_AKEEBA_DISCOVER_LABEL_FILES')
		</label>
        <select name="files[]" id="files" multiple="multiple" size="10">
			@foreach($this->files as $file)
                <option value="{{ basename($file) }}">{{ basename($file) }} </option>
			@endforeach
        </select>
        <p class="akeeba-help-text">
            @lang('COM_AKEEBA_DISCOVER_LABEL_SELECTFILES')
        </p>
	</div>

    <div class="akeeba-form-group--pull-right">
        <div class="akeeba-form-group--actions">
            <button class="akeeba-btn--primary" type="submit">
                <span class="akion-ios-upload"></span>
                @lang('COM_AKEEBA_DISCOVER_LABEL_IMPORT')
            </button>
        </div>
    </div>
    @endif

    @unless($hasFiles)
        <div class="akeeba-panel--warning">
		@lang('COM_AKEEBA_DISCOVER_ERROR_NOFILES')
	</div>

        <p>
		<button onclick="this.form.submit(); return false;" class="akeeba-btn--orange">@lang('COM_AKEEBA_DISCOVER_LABEL_GOBACK')</button>
	</p>
	 @endunless

    <div class="akeeba-hidden-fields-container">
	    @if($hasFiles)
            <input type="hidden" name="directory" value="{{{ $this->directory }}}" />
	    @endif
        <input type="hidden" name="token" value="@token()" />
    </div>
</form>
