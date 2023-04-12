<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Awf\Text\Text;

defined('_AKEEBA') or die();

/** @var \Solo\View\Extradirs\Html $this */


?>
@include('CommonTemplates/ErrorModal')
@include('CommonTemplates/FolderBrowser')
@include('CommonTemplates/ProfileName')

<div class="akeeba-container--primary">
    <div id="ak_list_container">
        <table id="ak_list_table" class="akeeba-table--striped--dynamic-line-editor">
            <thead>
            <tr>
                <!-- Delete -->
                <th width="50px">&nbsp;</th>
                <!-- Edit -->
                <th width="100px">&nbsp;</th>
                <!-- Directory path -->
                <th>
						<span rel="popover" data-original-title="@lang('COM_AKEEBA_INCLUDEFOLDER_LABEL_DIRECTORY')"
                              data-content="@lang('COM_AKEEBA_INCLUDEFOLDER_LABEL_DIRECTORY_HELP')">
							@lang('COM_AKEEBA_INCLUDEFOLDER_LABEL_DIRECTORY')
						</span>
                </th>
                <!-- Directory path -->
                <th>
						<span rel="popover" data-original-title="@lang('COM_AKEEBA_INCLUDEFOLDER_LABEL_VINCLUDEDIR')"
                              data-content="@lang('COM_AKEEBA_INCLUDEFOLDER_LABEL_VINCLUDEDIR_HELP')">
							@lang('COM_AKEEBA_INCLUDEFOLDER_LABEL_VINCLUDEDIR')
						</span>
                </th>
            </tr>
            </thead>
            <tbody id="ak_list_contents">
            </tbody>
        </table>
    </div>
</div>
