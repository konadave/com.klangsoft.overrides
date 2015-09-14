{literal}
	<style type="text/css">
		#extension-list {
			float: right;
			margin-left: 50px;
			border: 1px solid silver;
		}
		.extension-overrides {
			margin: 20px 5px;
		}
		.warn,
		.overridden {
			font-weight: bold;
			color: red;
		}
	</style>
{/literal}
<hr />
{if !$writeable}
	<p class="warn">CiviCRM needs write access to the {$ext_dir} directory in order to save snapshot.</p>
{/if}
<div id="extension-list">
	{foreach from=$extensions key=name item=files}
		<div class="extension-overrides">
			<p><strong>{$name}</strong></p>
			<ul>
				{foreach from=$files item=file}
					<li{if $core[$file]['changed']} class="overridden"{/if}>{$file}</li>
				{/foreach}
			</ul>
		</div>
	{foreachelse}
		<p><strong>None of your extensions are overriding any core files</strong>.</p>
	{/foreach}
</div>
<p>The list to the right shows which extensions are overridding core files, and
	which files they override. Any file listed in red has been changed in core since the
	last snapshot was saved.</p>
{if $writeable}
	<form method="POST">
		<input type="hidden" name="snapshot" value="{$snapshot}" />
		<input type="submit" value="Save Snapshot" />
	</form>
{/if}
{if $multiple}
	<hr />
	<p class="warn">WARNING: The following files are overridden by more than one extension.</p>
	<ul>
		{foreach from=$core key=name item=info}
			{if $info['multiple']}
				<li>{$name}</li>
			{/if}
		{/foreach}
	</ul>
{/if}
<div class="clear"></div>