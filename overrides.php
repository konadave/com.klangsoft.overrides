<?php

require_once 'overrides.civix.php';
use CRM_Overrides_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function overrides_civicrm_config(&$config) {
  _overrides_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function overrides_civicrm_xmlMenu(&$files) {
  _overrides_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function overrides_civicrm_install() {

  CRM_Core_DAO::executeQuery("
    CREATE TABLE `klangsoft_overrides` (
      `snapshot` text NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

  CRM_Core_DAO::executeQuery("INSERT INTO `klangsoft_overrides` VALUES ('a:0:{}');");

  \Civi::settings()->set('com.klangsoft_overrides:last_snapshot', '');

  _overrides_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function overrides_civicrm_uninstall() {

  CRM_Core_DAO::executeQuery("DROP TABLE `klangsoft_overrides`;");

  _overrides_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function overrides_civicrm_enable() {
  _overrides_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function overrides_civicrm_disable() {
  _overrides_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function overrides_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _overrides_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function overrides_civicrm_managed(&$entities) {
  _overrides_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function overrides_civicrm_caseTypes(&$caseTypes) {
  _overrides_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function overrides_civicrm_angularModules(&$angularModules) {
_overrides_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function overrides_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _overrides_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function overrides_civicrm_navigationMenu(&$menu) {
  _overrides_civix_insert_navigation_menu($menu, 'Administer/Administration Console', array(
    'label'      => E::ts('Extension File Overrides'),
    'name'       => 'overrides_admin',
    'url'        => 'civicrm/admin/overrides',
    'permission' => 'administer CiviCRM',
    'operator'   => 'OR',
    'separator'  => 0,
  ));
  _overrides_civix_navigationMenu($menu);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function overrides_civicrm_preProcess($formName, &$form) {

}

*/
