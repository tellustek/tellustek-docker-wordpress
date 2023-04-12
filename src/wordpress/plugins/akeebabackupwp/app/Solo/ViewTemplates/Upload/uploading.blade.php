<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_AKEEBA') or die();

/** @var \Solo\View\Upload\Html $this */

?>
<form action="@route('index.php?view=Upload&task=upload&tmpl=component&id=' . (int) $this->id)" method="POST" name="akeebaform">
	<input type="hidden" name="part" value="{{ $this->part }}" />
	<input type="hidden" name="frag" value="{{ $this->frag }}" />
</form>

<div class="akeeba-panel--information">
    <p>
	    @if($this->frag == 0)
            @sprintf('COM_AKEEBA_TRANSFER_MSG_UPLOADINGPART', $this->part+1, max($this->parts, 1))
	    @else
		    @sprintf('COM_AKEEBA_TRANSFER_MSG_UPLOADINGFRAG', $this->part+1, max($this->parts, 1), max(++$this->frag, 1))
	    @endif
    </p>
</div>