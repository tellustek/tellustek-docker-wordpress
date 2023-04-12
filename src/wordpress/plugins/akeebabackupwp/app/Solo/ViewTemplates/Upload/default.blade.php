<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_AKEEBA') or die();

/** @var \Solo\View\Upload\Html $this */

?>
<form action="@route('index.php?view=Upload&task=upload&tmpl=component&id=' . (int) $this->id)" method="POST" name="akeebaform" id="akeebaform">
    <input type="hidden" name="part" value="0"/>
    <input type="hidden" name="frag" value="0"/>
</form>

<div class="akeeba-panel--information">
    <p>
		@lang('COM_AKEEBA_TRANSFER_MSG_START')
    </p>
</div>