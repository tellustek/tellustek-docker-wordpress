<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_AKEEBA') or die();

/** @var   \Solo\View\Restore\Html $this */

?>

@include('CommonTemplates/FTPBrowser')
@include('CommonTemplates/FTPConnectionTest')
@include('CommonTemplates/ErrorModal')

<form action="@route('index.php?view=restore&task=start&id=' . $this->id)" method="POST"
      name="adminForm" id="adminForm" class="akeeba-form--horizontal" role="form">
    <input type="hidden" name="token" value="@token()">

    <h4>@lang('COM_AKEEBA_RESTORE_LABEL_EXTRACTIONMETHOD')</h4>

    <div class="akeeba-form-group">
        <label for="procengine">
            @lang('COM_AKEEBA_RESTORE_LABEL_EXTRACTIONMETHOD')
        </label>
        @html('select.genericList', $this->extractionmodes, 'procengine', [], 'value', 'text', $this->ftpparams['procengine'])
        <p class="akeeba-help-text">
            @lang('COM_AKEEBA_RESTORE_LABEL_REMOTETIP')
        </p>
    </div>

    @if($this->getContainer()->appConfig->get('showDeleteOnRestore', 0) == 1)
        <div class="akeeba-form-group">
            <label for="zapbefore">
                @lang('COM_AKEEBA_RESTORE_LABEL_ZAPBEFORE')
            </label>
            <div class="akeeba-toggle">
                {{ \Solo\Helper\FEFSelect::booleanList('zapbefore', array('forToggle' => 1, 'colorBoolean' => 1), 0) }}
            </div>
            <p class="akeeba-help-text">
                @lang('COM_AKEEBA_RESTORE_LABEL_ZAPBEFORE_HELP')
            </p>
        </div>
    @endif

    <div class="akeeba-form-group">
        <label for="stealthmode">
            @lang('COM_AKEEBA_RESTORE_LABEL_STEALTHMODE')
        </label>
        <div class="akeeba-toggle">
            {{ \Solo\Helper\FEFSelect::booleanList('stealthmode', array('forToggle' => 1, 'colorBoolean' => 1), 0) }}
        </div>
        <p class="akeeba-help-text">
            @lang('COM_AKEEBA_RESTORE_LABEL_STEALTHMODE_HELP')
        </p>
    </div>

@if ($this->extension == 'jps')
        <h4>
            @lang('COM_AKEEBA_RESTORE_LABEL_JPSOPTIONS')
        </h4>

        <div class="akeeba-form-group">
            <label for="jps_key">
                @lang('COM_AKEEBA_CONFIG_JPS_KEY_TITLE')
            </label>
            <input value="" type="password" class="form-control" id="jps_key" name="jps_key"
                   placeholder="@lang('COM_AKEEBA_CONFIG_JPS_KEY_TITLE')" autocomplete="off">
        </div>
    @endif

    <div id="ftpOptions">
        <h4>@lang('COM_AKEEBA_RESTORE_LABEL_FTPOPTIONS')</h4>

        <input id="ftp_passive_mode" type="checkbox" checked autocomplete="off" style="display: none">
        <input id="ftp_ftps" type="checkbox" autocomplete="off" style="display: none">
        <input id="ftp_passive_mode_workaround" type="checkbox" autocomplete="off" style="display: none">

        <div class="akeeba-form-group">
            <label for="ftp_host">
                @lang('COM_AKEEBA_CONFIG_DIRECTFTP_HOST_TITLE')
            </label>

            <input id="ftp_host" name="ftp_host" value="{{{ $this->ftpparams['ftp_host'] }}}"
                   type="text" class="form-control">
        </div>

        <div class="akeeba-form-group">
            <label for="ftp_port">
                @lang('COM_AKEEBA_CONFIG_DIRECTFTP_PORT_TITLE')
            </label>

            <input id="ftp_port" name="ftp_port" value="{{{ $this->ftpparams['ftp_port'] }}}"
                   type="text" class="form-control">
        </div>

        <div class="akeeba-form-group">
            <label for="ftp_user">
                @lang('COM_AKEEBA_CONFIG_DIRECTFTP_USER_TITLE')
            </label>

            <input id="ftp_user" name="ftp_user" value="{{{ $this->ftpparams['ftp_user'] }}}"
                   type="text" class="form-control">
        </div>

        <div class="akeeba-form-group">
            <label for="ftp_pass">
                @lang('COM_AKEEBA_CONFIG_DIRECTFTP_PASSWORD_TITLE')
            </label>

            <input id="ftp_pass" name="ftp_pass" value="{{{ $this->ftpparams['ftp_pass'] }}}"
                   type="password" class="form-control">
        </div>

        <div class="akeeba-form-group">
            <label for="ftp_initial_directory">
                @lang('COM_AKEEBA_CONFIG_DIRECTFTP_INITDIR_TITLE')
            </label>
            <input id="ftp_initial_directory" name="ftp_root" value="{{{ $this->ftpparams['ftp_root'] }}}" type="text" />
        </div>
    </div>

    <h4>@lang('COM_AKEEBA_RESTORE_LABEL_TIME_HEAD')</h4>

    <div class="akeeba-form-group">
        <label for="min_exec">
            @lang('COM_AKEEBA_RESTORE_LABEL_MIN_EXEC')
        </label>
        <input type="number" min="0" max="180" name="min_exec"
               value="{{ $this->getModel()->getState('min_exec', 0, 'int')  }}" />
        <p class="akeeba-help-text">
            @lang('COM_AKEEBA_RESTORE_LABEL_MIN_EXEC_TIP')
        </p>
    </div>
    <div class="akeeba-form-group">
        <label for="max_exec">
            @lang('COM_AKEEBA_RESTORE_LABEL_MAX_EXEC')
        </label>
        <input type="number" min="0" max="180" name="max_exec"
               value="{{ $this->getModel()->getState('max_exec', 5, 'int') }}" />
        <p class="akeeba-help-text">
            @lang('COM_AKEEBA_RESTORE_LABEL_MAX_EXEC_TIP')
        </p>
    </div>

    <hr />

    <div class="akeeba-form-group--pull-right">
        <div class="akeeba-form-group--actions">
            <button class="akeeba-btn--primary" id="backup-start">
                <span class="akion-refresh"></span>
                @lang('COM_AKEEBA_RESTORE_LABEL_START')
            </button>
            <button class="akeeba-btn--grey" id="testftp">
                <span class="akion-ios-pulse-strong"></span>
                @lang('COM_AKEEBA_CONFIG_DIRECTFTP_TEST_TITLE')
            </button>
        </div>
    </div>

</form>
