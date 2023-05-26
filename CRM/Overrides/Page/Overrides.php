<?php

// phpcs:disable
use CRM_Overrides_ExtensionUtil as E;
// phpcs:enable

class CRM_Overrides_Page_Overrides extends CRM_Core_Page {

  function run() {

    $fn = CRM_Utils_Request::retrieve('zn', 'String');
    if ($fn) {
      $dir = Civi::paths()->getPath('[civicrm.files]/.overrides');
      $zn = "$dir/$fn.zip";
      $buf = file_get_contents($zn);
      unlink($zn);
      CRM_Utils_System::download("$fn.zip", 'application/zip', $buf);
      return;
    }

    $coreDir = Civi::paths()->getPath('[civicrm.root]/.');
    $coreVer = CRM_Utils_System::version();
    $releases = _overrides_getAllReleases();
    [$extensions, $overrides] = _overrides_findOverrides();

    foreach ($overrides as $fn => $override) {
      if (count($override['extensions']) > 1) {
        foreach ($override['extensions'] as $key => $ver) {
          $extensions[$key]['overrides'][$fn]['conflict'] = TRUE;
        }
      }
      foreach ($override['extensions'] as $key => $ver) {
        if ($override['md5']['local'] !== $override['md5'][$coreVer]) {
          $extensions[$key]['overrides'][$fn]['local'] = TRUE;
        }
        if ($override['md5'][$coreVer] != $override['md5'][$ver]) {
          $extensions[$key]['overrides'][$fn]['changed'] = TRUE;
        }
        if ($releases[$ver] >= $releases[$coreVer] && $extensions[$key]['overrides'][$fn]['changed']) {
          $extensions[$key]['overrides'][$fn]['future'] = TRUE;
        }
      }
    }

    require_once(E::path("Parsedown.php"));
    $parsedown = new Parsedown();
    $help = $parsedown->text(file_get_contents(E::path('docs/instructions.md')));

    $this->assign('extensions', $extensions);
    $this->assign('overrides', $overrides);
    $this->assign('coreVer', $coreVer);
    $this->assign('canDownload', class_exists('ZipArchive'));
    $this->assign('instructions', $help);

    Civi::resources()->addVars('overrides', [
      'extensions' => $extensions,
      'overrides' => $overrides,
      'coreVer' => $coreVer,
      'instructions' => $help
    ]);

    Civi::resources()->addScriptFile('com.klangsoft.overrides', 'js/overrides.js');
    Civi::resources()->addScriptFile('com.klangsoft.overrides', 'js/diff-match-patch/diff_match_patch.js');
    Civi::resources()->addStyleFile('com.klangsoft.overrides', 'css/overrides.css');
    Civi::resources()->addStyleUrl('https://cdnjs.cloudflare.com/ajax/libs/github-markdown-css/5.2.0/github-markdown.min.css');

    parent::run();
  }

}