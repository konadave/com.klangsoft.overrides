{literal}
	<style type="text/css">
		#extension-list {
			float: right;
			margin-left: 50px;
			border: 1px solid silver;
			max-width: 50%;
		}
		#extension-list p {
			padding: 0px 10px;
		}
		.extension-overrides {
			margin: 20px 5px;
		}
		.warn,
		.overridden {
			font-weight: bold;
			color: red;
		}
		.addition {
			font-weight: bold;
			color: blue;
		}
	</style>
{/literal}
<hr />
<div id="extension-list">
	{foreach from=$extensions key=name item=files}
		<div class="extension-overrides">
			<p><strong>{$name}</strong></p>
			<ul>
				{foreach from=$files item=file}
					<li{if $core[$file]['changed']} class="overridden"{elseif $core[$file]['is_new']} class="addition"{/if}>{$file}</li>
				{/foreach}
			</ul>
		</div>
	{foreachelse}
		<p><strong>None of your extensions are overriding any core files</strong>.</p>
	{/foreach}
</div>
<p>An extension may supply modified copies of core PHP and template files, and by doing so causes CiviCRM to use those files in place of the original so long as the extension is enabled. There's nothing wrong with doing this; some functionality would be very difficult, if not impossible, to implement otherwise. The only downside is that they can be hard to maintain.</p>

<p>So what happens when the core file that the extension has overridden gets modified? CiviCRM will continue to use the overriden file provided by the extension. This means a security patch may not get applied, or you might miss out on some new feature that was added, etc.</p>

<p><strong>The list to the right shows which extensions are overridding core files, and
	which files they override. Any file listed in <span class="overridden">red</span> has been changed in core since the
	last snapshot was saved. Any file listed in <span class="addition">blue</span> has been added since the last snapshot was
	saved, so you should save a new snapshot now.</strong></p>

<p>If a file is shown as changed, you should first notify the maintainer of the extension to let them know that their extension may need to be updated to work with the latest core file(s). If you are unable to reach the maintainer, or they're not interested in doing anything about it, then you or someone in your IT department should look into what changed and merge them into the extension's overrides. The other option is to disable the extension.</p>

<p>After all changes have been resolved, save the current snapshot.</p>

<form method="POST">
	<input type="hidden" name="snapshot" value="{$snapshot}" />
	<input type="submit" value="Save Snapshot" /><br />
	{if $last_snapshot}<p>Last saved {$last_snapshot}</p>{/if}
</form>
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