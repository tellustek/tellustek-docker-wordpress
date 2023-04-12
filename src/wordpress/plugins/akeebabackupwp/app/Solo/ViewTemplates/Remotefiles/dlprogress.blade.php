<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_AKEEBA') or die();

/** @var \Solo\View\Remotefiles\Html $this */

?>
<div class="akeeba-progress">
	<div class="akeeba-progress-fill" role="progressbar" aria-valuenow="{{ sprintf('%0.2f', $this->percent) }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ sprintf('%0.0f', $this->percent) }}%;">
	</div>
    <div class="akeeba-progress-status">{{ sprintf('%0.2f', $this->percent) }}%</div>
</div>

<div class="akeeba-panel--information">
	@sprintf('COM_AKEEBA_REMOTEFILES_LBL_DOWNLOADEDSOFAR', $this->done, $this->total, $this->percent)
</div>

<form action="@route('index.php?view=remotefiles&task=downloadToServer&tmpl=component')" name="adminForm" id="adminForm" method="post">
	<input type="hidden" name="id" value="{{ (int)$this->id }}"/>
	<input type="hidden" name="part" value="{{ (int)$this->part }}"/>
	<input type="hidden" name="frag" value="{{ (int)$this->frag }}"/>
</form>
