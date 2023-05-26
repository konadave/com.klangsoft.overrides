<?php

require_once 'overrides.civix.php';
// phpcs:disable
use CRM_Overrides_ExtensionUtil as E;
// phpcs:enable

/**
 * Assemble all versions and release dates back to 4.7.14
 *
 * @return array key = version, value = release date
 */
function _overrides_getAllReleases() {
  static $versions;
  static $dates;
  static $releases;

  if (!$releases) {
    $notes = file_get_contents('https://raw.githubusercontent.com/civicrm/civicrm-core/master/release-notes.md');
  
    $versions = [];
    preg_match_all('/^## CiviCRM (.+?)$/m', $notes, $versions);
  
    $dates = [];
    preg_match_all('/^Released (.+?)$/m', $notes, $dates);
    array_walk($dates[1], function(&$val, $key) {
      $val = strtotime($val);
    });
    $releases = array_combine($versions[1], $dates[1]);
  }
  return $releases;
}

/**
 * Get file contents for a given version
 *
 * @param string $override /path/to/file.ext
 * @param string $version From which version of CiviCRM to get contents for
 * @param bool $md5 Return MD5 instead of contents?
 * @return string The contents or md5 hash of the rquested file
 */
function _overrides_getFileVersion($override, $version, $md5 = FALSE) {
  $dir = Civi::paths()->getPath('[civicrm.files]/.overrides');
  if (!is_dir($dir)) {
    mkdir($dir);
    file_put_contents("$dir/README", 'This directory is essentially a cache between this site and github.com, used by the Extension File Overrides extension to determine what and when changes are made to core files that have been overriden by an extesion. If this directory or any of it\'s contents are deleted, they will be downloaded again and recreated as needed.');
  }
  $fn = $dir . '/' . str_replace('/', '_', $override) . "-$version";

  if (!file_exists($fn)) {
    $contents = file_get_contents("https://raw.githubusercontent.com/civicrm/civicrm-core/$version/$override");
    file_put_contents($fn, $contents);
  }
  if ($md5) {
    return !empty($contents) ? md5($contents) : md5_file($fn);
  }
  return $contents ?? file_get_contents($fn);
}

/**
 * Scan for overridden files
 *
 * @param string $dir The base directory to scan
 * @param string $rel Relative path to scan
 * @return array List of all file overrides in the given directory
 */
function _overrides_scanDir($dir, $rel = '') {
  static $coreDir;
  if (!$coreDir) {
    $coreDir = Civi::paths()->getPath('[civicrm.root]/.');
  }
  $overrides = [];

  if ($rel && !is_dir("$coreDir/$rel")) {
    return $overrides;
  }
  $extDir = "$dir/$rel";
  if ($dh = @opendir($extDir)) {
    while (($fn = readdir($dh)) !== FALSE) {
      if ($fn[0] == '.') {
        continue;
      }
      $subRel = "$rel/$fn";
      if (is_dir("$extDir/$fn")) {
        $overrides = array_merge($overrides, _overrides_scanDir($dir, $subRel));
      }
      elseif (file_exists("$coreDir/$subRel")) {
        $overrides[] = $subRel;
      }
    }
    closedir($dh);
  }
  return $overrides;
}

/**
 * Determine current CiviVersion at a given timestamp
 *
 * @param int $ts Unix timestamp
 * @return string CiviCRM version at that time
 */
function _overrides_versionAtTime($ts) {
  $releases = _overrides_getAllReleases();
  foreach ($releases as $version => $date) {
    if ($date <= $ts) {
      break;
    }
  }
  return $version;
}

/**
 * Find all extension overrides
 *
 * @return void
 */
function _overrides_findOverrides() {
  $extensions = [];

  // we can't use api4 because it doesn't return enough info
  $api = civicrm_api3('Extension', 'get', [
    'options' => [
      'limit' => 0,
      'sort' => 'label ASC'
    ]
  ]);
  foreach ($api['values'] as $ext) {
    if (in_array($ext['status'], ['disabled', 'installed'])) {
      $fns = array_merge(
        _overrides_scanDir($ext['path'], 'api'),
        _overrides_scanDir($ext['path'], 'Civi'),
        _overrides_scanDir($ext['path'], 'CRM'),
        _overrides_scanDir($ext['path'], 'templates')
      );
      if (!empty($fns)) {
        $ts = strtotime($ext['releaseDate']);
        $overrides = array_combine($fns, array_fill(0, count($fns), [
          'conflict' => FALSE,
          'changed' => FALSE,
          'local' => FALSE,
          'future' => FALSE
        ]));

        $extensions[$ext['key']] = [
          'label' => $ext['label'],
          'description' => $ext['description'],
          'releaseDate' => CRM_Utils_Date::formatDateOnlyLong(date('Y-m-d', $ts)),
          'version' => $ext['version'],
          'civiVersion' => _overrides_versionAtTime($ts),
          'path' => $ext['path'],
          'status' => $ext['status'],
          'overrides' => $overrides
        ];
      }
    }
  }

  $coreDir = Civi::paths()->getPath('[civicrm.root]/.');
  $coreVer = CRM_Utils_System::version();

  $overrides = [];
  foreach ($extensions as $key => $ext) {
    if ($ext['status'] == 'installed') {
      $ver = $ext['civiVersion'];
      foreach ($ext['overrides'] as $fn => $flags) {
        if (empty($overrides[$fn])) {
          $overrides[$fn] = [
            'extensions' => [],
            'md5' => [
              'local' => md5_file("$coreDir/$fn"),
              $coreVer => _overrides_getFileVersion($fn, $coreVer, TRUE)
            ]
          ];
        }
        $overrides[$fn]['extensions'][$key] = $ver;
        if (empty($overrides[$fn]['md5'][$ver])) {
          $overrides[$fn]['md5'][$ver] = _overrides_getFileVersion($fn, $ver, TRUE);
        }
      }
    }
  }
  return [$extensions, $overrides];
}

/**
 * Implements hook_civicrm_check().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_check/
 */
function overrides_civicrm_check(&$messages, $statusNames, $includeDisabled) {
  $coreDir = Civi::paths()->getPath('[civicrm.root]/.');
  $coreVer = CRM_Utils_System::version();
  $releases = _overrides_getAllReleases();
  [$extensions, $overrides] = _overrides_findOverrides();

  $manageExt = CRM_Utils_System::url('civicrm/admin/extensions', 'reset=1');
  $manageOvr = CRM_Utils_System::url('civicrm/admin/overrides', 'reset=1');

  $conflicts = $changes = FALSE;
  
  foreach ($overrides as $fn => $override) {
    $conflicts = $conflicts || count($override['extensions']) > 1;

    foreach ($override['extensions'] as $key => $ver) {
      $changes = $changes || $override['md5'][$coreVer] != $override['md5'][$ver];
    }
  }

  if ($conflicts) {
    $messages[] = new CRM_Utils_Check_message(
      'com.klangsoft.overrides-conflict',
      "<p>Multiple extensions have overriden the same core file, which is an impossible situation. Please see the <a href=\"$manageOvr\">Extension File Overrides</a> page for details. One of the extensions will likely need to be disabled.</p>",
      'Conflicting Extensions Found',
      \Psr\Log\LogLevel::CRITICAL,
      'fa-plug'
    );
  }
  if ($changes) {
    $messages[] = new CRM_Utils_Check_message(
      'com.klangsoft.overrides-changes',
      "<p>Changes have been detected in core file(s) that have been overridden by an extension. Please see the <a href=\"$manageOvr\">Extension File Overrides</a> page for details.</p>",
      "Extension Override Changed",
      \Psr\Log\LogLevel::CRITICAL,
      'fa-plug'
    );
  }
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu/
 */
function overrides_civicrm_xmlMenu(&$files) {
  $files[] = dirname(__FILE__) . '/xml/Menu/overrides.xml';
}

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function overrides_civicrm_config(&$config): void {
  _overrides_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *  
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function overrides_civicrm_install(): void {
  _overrides_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function overrides_civicrm_postInstall(): void {
  _overrides_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function overrides_civicrm_uninstall(): void {
  _overrides_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function overrides_civicrm_enable(): void {
  _overrides_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function overrides_civicrm_disable(): void {
  _overrides_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function overrides_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _overrides_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function overrides_civicrm_entityTypes(&$entityTypes): void {
  _overrides_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function overrides_civicrm_preProcess($formName, &$form): void {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
function overrides_civicrm_navigationMenu(&$menu): void {
  _overrides_civix_insert_navigation_menu($menu, 'Administer/Administration Console', [
    'label' => E::ts('Extension File Overrides'),
    'name' => 'extension_file_overrides',
    'url' => 'civicrm/admin/overrides',
    'permission' => 'administer CiviCRM',
    //'operator' => 'OR',
    //'separator' => 0,
  ]);
  _overrides_civix_navigationMenu($menu);
}
