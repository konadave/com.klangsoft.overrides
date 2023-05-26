(function($, settings) {

  const overrides = {

    sources: null,
    diffs: null,
    patches: null,
    key: null,
    ext: null,
    fn: null,

    $diff: null,
    $nav: null,
    $d: null,
    $help: null,
    $counter: null,
    $changes: null,
    $choices: null,
    $closer: null,
    $cleaners: null,

    entityMap: {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;',
      '/': '&#x2F;',
      '`': '&#x60;',
      '=': '&#x3D;'
    },
    escapeHtml: function(str) {
      return String(str).replace(/[&<>"'`=\/]/g, (s) => {
        return this.entityMap[s];
      });
    },
    
    _calcDiff: function(older, newer) {
      const dmp = new diff_match_patch();
      dmp.Diff_Timeout = 0;
      const a = dmp.diff_linesToChars_(older, newer);
      const diff = dmp.diff_main(a.chars1, a.chars2, false);
      dmp.diff_charsToLines_(diff, a.lineArray);

      return diff;
    },
    calcDiffs: function() {
      this.diffs = [false];
      
      this.diffs.push(this._calcDiff(this.sources[this.ext.civiVersion], this.sources.ext));
      this.diffs.push(this._calcDiff(this.sources[this.ext.civiVersion], this.sources[settings.coreVer]));

      if (this.ext.overrides[this.fn].local) {
        this.diffs.push(this._calcDiff(this.sources[settings.coreVer], this.sources.local));
      } else {
        this.diffs.push(false);
      }

      this.diffs.push(this._calcDiff(this.sources[settings.coreVer], this.sources.patchedExt2Core));
      this.diffs.push(this._calcDiff(this.sources[settings.coreVer], this.sources.patchedCore2Ext));
    },

    changeDiff: function(i) {
      let added = 0, removed = 0;
      this.$diff.empty();

      this.diffs[i].forEach((change) => {
        const node = $('<span>')
          .html(this.escapeHtml(change[1]))
          .appendTo(this.$diff)
          .click(this._clickDiff);

        if (change[0] !== 0) {
          node.prop('ichange', added + removed);
        }
        if (change[0] === -1) {
          node.addClass('removed');
          removed += 1;
        } else {
          if (change[0] === 1) {
            node.addClass('added');
            added += 1;
          }
        }
      });

      this.$nav.removeClass('not-visible');
      this.$nav.find('.added').text(added + ' addition' + (added != 1 ? 's' : ''));
      this.$nav.find('.removed').text(removed + ' deletion' + (removed != 1 ? 's' : ''));

      this.$d.removeClass('hidden');
      this.$help.addClass('hidden');

      this.$changes = this.$diff.find('.added, .removed');
      this.$counter.prop('ichange', 1).trigger('click');
    },

    clickCounter: function(evt) {
      let i = this.$counter.prop('ichange');
      
      if (!isNaN(i) && this.$changes.length > 1) {
        const $target = $(evt.target);
        let delta = 0;
  
        if ($target.hasClass('fa-chevron-up')) {
          delta = -1;
        }
        else if ($target.hasClass('fa-chevron-down')) {
          delta = 1;
        }
        else {
          delta = evt.offsetX >= Math.round(this.$counter.prop('clientWidth') / 2) ? 1 : -1;
        }

        this.$changes.removeClass('active');
        i += delta;
        if (i === this.$changes.length) {
          i = 0;
        }
        else if (i < 0) {
          i = this.$changes.length - 1;
        }
        $(this.$changes[i]).addClass('active');
        this.$counter.prop('ichange', i);
        this.$counter.find('.of').html(`${i + 1} of ${this.$changes.length}`);
      }
      if (i < this.$changes.length) {
        this.$changes[i].scrollIntoView({
          behavior: 'smooth',
          block: 'nearest'
        });
      }
    },

    clickDiff: function(evt) {
      this.$counter.prop('ichange', $(evt.target).prop('ichange') + 1).trigger('click');
    },
    _clickDiff: null,

    clickOverride: function($li) {
      const key = this.key = $li.attr('data-extension-key');
      const ext = this.ext = settings.extensions[key];
      const fn = this.fn = $li.attr('data-override');

      this.loadItUp(key, ext, fn);

      this.$d.removeClass('hidden');
      this.$d.find('.ext-name').text(ext.label);
      this.$d.find('.ext-ver').text(ext.civiVersion);
      this.$d.find('.ext-override').text(fn);

      this.$choices.prop('checked', false);
      $(this.$choices[1]).prop('disabled', ext.overrides[fn].changed === false);
      $(this.$choices[2]).prop('disabled', ext.overrides[fn].local === false);
      $(this.$choices[3]).prop('disabled', ext.overrides[fn].changed === false && ext.overrides[fn].local === false);
      $(this.$choices[4]).prop('disabled', ext.overrides[fn].changed === false && ext.overrides[fn].local === false);
      this.$cleaners.removeClass('diff-clean');

      this.$nav.addClass('not-visible');
      this.$help.addClass('hidden');
      this.$closer.removeClass('hidden');
    },

    closeOverride: function() {
      this.$d.addClass('hidden');
      this.$help.removeClass('hidden').html(settings.instructions);
      this.$closer.addClass('hidden');
    },

    downloadDiff: function() {
      this.$d.addClass('hidden');
      this.$help.removeClass('hidden');
    },

    loadItUp: function(key, ext, fn) {
      this.diffs = null;
      this.sources = null;
      this.$diff.text('Loading...');

      CRM.api3('Overrides', 'sources', {
        key, fn,
        ver: ext.civiVersion,
        coreVer: settings.coreVer,
        local: ext.overrides[fn].local !== false
      })
      .then((result) => {
        this.sources = result;

        this.patchThingsUp();
        this.calcDiffs();
        
        const choice = parseInt(this.$choices.find(':checked').val());
        if (!isNaN(choice)) {
          this.changeDiff(choice);
        } else {
          this.$diff.text('^ select an option above to view changes ^');
        }

        const one = JSON.stringify(this.diffs[1].filter((change) => change[0] !== 0));
        const four = JSON.stringify(this.diffs[4].filter((change) => change[0] !== 0));
        const five = JSON.stringify(this.diffs[5].filter((change) => change[0] !== 0));

        if (one.localeCompare(four) === 0) {
          $(this.$cleaners[0]).addClass('diff-clean');
        }
        if (one.localeCompare(five) === 0) {
          $(this.$cleaners[1]).addClass('diff-clean');
        }
      });
    },

    _patchIt: function(base, changed, applyTo) {
      const dmp = new diff_match_patch();
      dmp.Diff_Timeout = 0;
      const diff = dmp.diff_main(base, changed);
      dmp.diff_cleanupSemantic(diff);
      const patches = dmp.patch_make(base, diff);

      this.patches.push(dmp.patch_toText(patches));

      const result = dmp.patch_apply(patches, applyTo);
      return result[0];
    },

    patchThingsUp: function() {
      this.patches = [];
      this.sources.patchedExt2Core = this.sources[settings.coreVer];
      this.sources.patchedCore2Ext = this.sources.ext;

      if (this.ext.overrides[this.fn].changed) {
        this.sources.patchedExt2Core = this._patchIt(this.sources[this.ext.civiVersion], this.sources.ext, this.sources.patchedExt2Core);
        this.sources.patchedCore2Ext = this._patchIt(this.sources[this.ext.civiVersion], this.sources[settings.coreVer], this.sources.patchedCore2Ext);
      }
      if (this.ext.overrides[this.fn].local) {
        this.sources.patchedExt2Core = this._patchIt(this.sources[settings.coreVer], this.sources.local, this.sources.patchedExt2Core);
        this.sources.patchedCore2Ext = this._patchIt(this.sources[settings.coreVer], this.sources.local, this.sources.patchedCore2Ext);
      }

      CRM.api3('Overrides', 'download', {
        key: this.key,
        fn: this.fn,
        civiVersion: this.ext.civiVersion,
        coreVer: settings.coreVer,
        patches: this.patches,
        ext2Core: this.sources.patchedExt2Core,
        core2Ext : this.sources.patchedCore2Ext
      })
        .then((result) => {
          if (result.is_error === 0) {
            this.$d.find('#diff-download').attr('href', window.location.pathname + '?reset=1&zn=' + result.zn);
            this.$help.html(result.html);
          }
        });
    },

    init: function() {
      $('.override').click((evt) => this.clickOverride($(evt.currentTarget)));
      this.$closer = $('#diff-close').click(() => this.closeOverride());
      this._clickDiff = this.clickDiff.bind(this);
      this.$help = $('#diff-help');

      this.$d = $('#differator');
      this.$diff = this.$d.find('#the-diff');
      this.$nav = this.$d.find('#diff-nav');
      this.$cleaners = this.$d.find('.diff-cleaner');

      this.$choices = this.$d.find('input[name="choice"]')
        .click((evt) => this.changeDiff(parseInt($(evt.target).val())));

      this.$counter = this.$d.find('#diff-count')
        .click((evt) => this.clickCounter(evt));

      this.$d.find('#diff-download').click(() => this.downloadDiff());
    }
  }

  overrides.init();


}(CRM.$, CRM.vars.overrides));
