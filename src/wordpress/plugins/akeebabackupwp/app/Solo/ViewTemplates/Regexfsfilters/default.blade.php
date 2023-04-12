<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_AKEEBA') or die();

/** @var \Solo\View\Regexfsfilters\Html $this */

?>

@include('CommonTemplates/ErrorModal')
@include('CommonTemplates/ProfileName')

<div class="akeeba-panel--information">
    <div class="akeeba-form-section">
        <div class="akeeba-form--inline">
            <label>@lang('COM_AKEEBA_FILEFILTERS_LABEL_ROOTDIR')</label>
            <span id="ak_roots_container_tab">
		    {{ $this->root_select }}
	        </span>
        </div>
    </div>
</div>

<div class="akeeba-container--primary">
    <div id="ak_list_container">
        <table id="table-container" class="akeeba-table--striped--dynamic-line-editor">
            <thead>
            <tr>
                <th width="120px">&nbsp;</th>
                <th width="250px">@lang('COM_AKEEBA_FILEFILTERS_LABEL_TYPE')</th>
                <th>@lang('COM_AKEEBA_FILEFILTERS_LABEL_FILTERITEM')</th>
            </tr>
            </thead>
            <tbody id="ak_list_contents" class="table-container">
            </tbody>
        </table>
    </div>
</div>

<p></p>
