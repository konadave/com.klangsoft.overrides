<?php

require_once 'overrides.civix.php';
// phpcs:disable
use CRM_Overrides_ExtensionUtil as E;
// phpcs:enable

function civicrm_api3_overrides_sources($params) {
  $result = [];
  try {
    $path = civicrm_api3('Extension', 'getvalue', [
      'key' => $params['key'],
      'return' => 'path'
    ]);
    $result['ext'] = file_get_contents("$path/{$params['fn']}");
    $result[$params['ver']] = _overrides_getFileVersion($params['fn'], $params['ver']);
    $result[$params['coreVer']] = _overrides_getFileVersion($params['fn'], $params['coreVer']);

    if (!empty($params['local'])) {
      $coreDir = Civi::paths()->getPath('[civicrm.root]/.');
      $result['local'] = file_get_contents("$coreDir/{$params['fn']}");
    }
  }
  catch (Exception $e) {
    $result['is_error'] = TRUE;
    $result['message'] = $e->getMessage();
  }
  return $result;
}

function civicrm_api3_overrides_download($params) {
  $coreDir = Civi::paths()->getPath('[civicrm.root]/.');
  $dir = Civi::paths()->getPath('[civicrm.files]/.overrides');

  $cn = str_replace('/', '_', $params['fn']);
  $fn = "{$params['key']}-$cn-{$params['coreVer']}";
  $zn = "$dir/$fn.zip";

  unlink($zn);
  
  $zip = new ZipArchive();
  if ($zip->open($zn, ZipArchive::CREATE) === TRUE) {
    // base version of override
    $nn = "$cn-{$params['civiVersion']}";
    $zip->addFile("$dir/$nn", "$fn/$nn");
    // current version of installed civi
    $nn = "$cn-{$params['coreVer']}";
    $zip->addFile("$dir/$nn", "$fn/$nn");

    // patches
    if (count($params['patches']) > 2) {
      array_pop($params['patches']);
    }
    foreach ($params['patches'] as $i => $patch) {
      $i++;
      $zip->addFromString("$fn/patch-{$i}.diff", $patch);
    }

    // merges
    $zip->addFromString("$fn/$cn-core2ext", $params['core2Ext']);
    $zip->addFromString("$fn/$cn-ext2Core", $params['ext2Core']);

    // README

    $releases = _overrides_getAllReleases();
    $ext = civicrm_api3('Extension', 'getsingle', [
      'key' => $params['key']
    ]);

    $smarty = CRM_Core_Smarty::singleton();

    $smarty->pushScope([
      'ext' => $ext,
      'fn' => $params['fn'],
      'cn' => str_replace('/', '_', $params['fn']),
      'numPatches' => count($params['patches']),
      'coreVer' => $params['coreVer'],
      'extVer' => $params['civiVersion'],
      'releaseDate' => date('Y-m-d', $releases[$params['coreVer']])
    ]);
    $md = $smarty->fetch('readme.tpl');
    $smarty->popScope();
    
    $zip->addFromString("$fn/README.md", $md);
    $zip->close();

    require_once(E::path("Parsedown.php"));
    $parsedown = new Parsedown();

    return [
      'is_error' => 0,
      'zn' => $fn,
      'html' => $parsedown->text($md)
    ];
  }

  return [
    'is_error' => 1,
    'message' => 'Unable to create zip for download.'
  ];
}
