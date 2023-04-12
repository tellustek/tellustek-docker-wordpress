<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_AKEEBA') or die;

/** @var  Solo\View\Alice\Html $this */

$js = <<< JS
akeeba.System.documentReady(function(){
	window.setTimeout(function() {
	  document.forms.adminForm.submit();
	}, 500);
});
JS;
?>
@inlineJs($js)

<div class="akeeba-panel--info">
    <header class="akeeba-block-header">
        <h3>
            @lang('COM_AKEEBA_ALICE_ANALYZE_LABEL_PROGRESS')
        </h3>
    </header>
    <h4>
        {{ $this->currentSection }}
    </h4>
    <p>
        {{ $this->currentCheck }}
    </p>
    <div class="akeeba-progress">
        <div class="akeeba-progress-fill" style="width:{{ $this->percentage }}%"></div>
        <div class="akeeba-progress-status">
            {{ $this->percentage }}%
        </div>
    </div>
    <p>
        <img src="@media("media://image/spinner.gif")"
             alt="@lang('COM_AKEEBA_ALICE_ANALYZE_LABEL_PROGRESS')" />
    </p>
</div>

<form name="adminForm" id="adminForm" action="@route('index.php')" method="post">
    <input name="view" value="Alice" type="hidden" />
    <input name="task" value="step" type="hidden" />
    <input type="hidden" name="token" value="@token()" />
</form>