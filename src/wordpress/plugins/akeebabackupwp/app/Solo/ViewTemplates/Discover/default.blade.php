<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Awf\Text\Text;

defined('_AKEEBA') or die();

/** @var \Solo\View\Discover\Html $this */

$router = $this->container->router;
?>
@include('CommonTemplates/FolderBrowser')

<?php if (AKEEBABACKUP_PRO): ?>
<div class="akeeba-block--info">
	@sprintf('COM_AKEEBA_DISCOVER_LABEL_S3IMPORT', $router->route('index.php?view=s3import'))
	<a class="akeeba-btn--teal--small" href="@route('index.php?view=s3import')">
		<span class="icon-box-add"></span>
		@lang('COM_AKEEBA_S3IMPORT')
	</a>
</div>
<?php endif; ?>

<form name="adminForm" id="adminForm" action="@route('index.php?view=discover&task=discover')" method="POST" class="akeeba-form--horizontal--with-hidden" role="form">
	<div class="akeeba-form-group">
		<label for="directory">
			@lang('COM_AKEEBA_DISCOVER_LABEL_DIRECTORY')
		</label>

		<div class="akeeba-input-group">
            <input type="text" name="directory" id="directory" value="{{ $this->directory }}"
                   class="form-control">
            <span class="akeeba-input-group-btn">
                <button title="@lang('COM_AKEEBA_CONFIG_UI_BROWSE')" class="akeeba-btn--inverse" id="browserbutton">
                    <span class="akion-folder"></span>
                </button>
            </span>
		</div>

        <p class="akeeba-help-text">
			@lang('COM_AKEEBA_DISCOVER_LABEL_SELECTDIR')
        </p>
	</div>

    <div class="akeeba-form-group--pull-right">
        <div class="akeeba-form-group--actions">
            <button class="akeeba-btn--primary" type="submit">
                @lang('COM_AKEEBA_DISCOVER_LABEL_SCAN')
            </button>
	    </div>
    </div>

    <div class="akeeba-hidden-fields-container">
        <input type="hidden" name="token" value="@token()" />
    </div>

</form>
