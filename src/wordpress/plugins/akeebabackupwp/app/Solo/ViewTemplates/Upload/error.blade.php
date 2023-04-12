<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Awf\Text\Text;

defined('_AKEEBA') or die();

/** @var \Solo\View\Upload\Html $this */

$errorParts = explode("\n", $this->errorMessage, 2);

?>
<div class="akeeba-panel--failure">
	<h3>
		@lang('COM_AKEEBA_TRANSFER_MSG_FAILED')
	</h3>
	<p>
		{{{ $errorParts[0] }}}
	</p>
	@if(isset($errorParts[1]))
		<pre>{{{ $errorParts[1] }}}</pre>
	@endif
</div>
