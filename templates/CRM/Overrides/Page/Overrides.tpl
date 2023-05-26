<div id="extension-file-overrides">
  
  <div id="overriders">
    {foreach from=$extensions item=ext key=key}
      <div class="extension{if $ext.status != 'installed'} disabled{/if}">
        <div class="title">{$ext.label}{if $ext.status != 'installed'} - Disabled{/if}</div>
        <div class="release">v{$ext.version}, {$ext.releaseDate} (CiviCRM {$ext.civiVersion})</div>
        <div class="description">{$ext.description}</div>
        <hr />
        <ul>
          {foreach from=$ext.overrides item=flags key=fn}
            <li class="override" data-extension-key="{$key}" data-override="{$fn}">
              <span class="fn{if $flags.conflict} conflict{/if}{if $flags.changed} changed{/if}">{$fn}</span>
              {if $flags.conflict}<i class="crm-i fa-bolt"></i>{/if}
              {if $flags.changed}<i class="crm-i fa-exclamation-triangle"></i>{/if}
              {if $flags.local}<i class="crm-i fa-server"></i>{/if}
              {if $flags.future}<i class="crm-i fa-clock-o"></i>{/if}
            </li>
          {/foreach}
        </ul>
      </div>
    {foreachelse}
      <p>Congratulations, no enabled extension is currently overriding a core file.</p>
    {/foreach}
  </div>

  <div id="diff-close" class="hidden"><i class="crm-i fa-times"></i></div>

  <div id="differator" class="hidden">
    <div id="diff-header">
      <h3 class="title ext-name"></h3>
      <div class="ext-override"></div>
    </div>
    <div id="diff-choices">
      <div class="choice">
        <input type="radio" name="choice" id="what-ext-changed" value="1" />
        <label for="what-ext-changed">#1 - Changes made by the extension</label>
        <div class="description">Comparing against CiviCRM <span class="ext-ver"></span></div>
      </div>

      <div class="choice">
        <input type="radio" name="choice" id="what-civi-changed" value="2" />
        <label for="what-civi-changed">#2 - Changes since made by CiviCRM</label>
        <div class="description">Comparing CiviCRM <span class="ext-ver"></span> against CiviCRM {$coreVer}</div>
      </div>

      <div class="choice">
        <input type="radio" name="choice" id="what-local-changed" value="3" />
        <label for="what-local-changed">#3 - Changes made on server filesystem</label>
        <div class="description">Comparing server file to CiviCRM {$coreVer}</div>
      </div>

      <div class="choice">
        <input type="radio" name="choice" id="what-ext-latest" value="4" />
        <label for="what-ext-latest" class="diff-cleaner">#4 - Merge override into latest</label>
        <div class="description">An automated attempt at merging #1 and #3 into #2</div>
      </div>

      <div class="choice">
        <input type="radio" name="choice" id="what-latest-ext" value="5" />
        <label for="what-latest-ext" class="diff-cleaner">#5 - Merge latest into override</label>
        <div class="description">An automated attempt at merging #2 and #3 into #1</div>
      </div>
    </div>

    <div id="diff-nav" class="not-visible">
      <div style="float:right">
        {if $canDownload}
          <a id="diff-download" class="button" href="#" target="_blank"><i class="crm-i fa-download"></i>&nbsp;Download</a>
        {/if}
        <a id="diff-count" class="button highlight">
          <i class="crm-i fa-chevron-up"></i>&nbsp;<span class="of"></span>&nbsp;<i class="crm-i fa-chevron-down"></i>
        </a>
      </div>
      <span class="diff-count removed"></span>
      <span class="diff-count added"></span>
    </div>

    <div id="the-diff">Loading...</div>
  </div>

  <div id="diff-help" class="markdown-body">{$instructions}</div>

  <div class="clear"></div>
</div>