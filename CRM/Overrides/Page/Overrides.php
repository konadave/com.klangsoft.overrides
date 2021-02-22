<?php

require_once 'CRM/Core/Page.php';

class CRM_Overrides_Page_Overrides extends CRM_Core_Page {

  protected $extensions = array();
  protected $core = array();

  function run() {
    CRM_Utils_System::setTitle(ts('Extension File Overrides'));

    $system = CRM_Extension_System::singleton();
    $manager = $system->getManager();
    $container = $system->getDefaultContainer();

    $keys = $container->getKeys();
    foreach($keys as $key) {
      $base_path = $container->getPath($key);
      $this->find_overrides($key, $base_path, '/api');
      $this->find_overrides($key, $base_path, '/Civi');
      $this->find_overrides($key, $base_path, '/CRM');
      $this->find_overrides($key, $base_path, '/templates');
    }

    $snapshot = $_SESSION['CiviCRM']['qfPrivateKey'];
    $this->look_for_changes(!empty($_POST['snapshot']) && ($_POST['snapshot'] == $snapshot));

    $multiple = false;
    foreach($this->core as &$core) {
      if (count($core['extensions']) > 1)
        $core['multiple'] = $multiple = true;
    }

    $extensions = $statuses = $friendly = array();
    foreach($this->extensions as $name => $files) {
      if (count($files) > 0) {
        $extensions[$name] = $files;
        $statuses[$name] = $manager->getStatus($name);
        $friendly[$name] = CRM_Core_DAO::singleValueQuery("SELECT name FROM civicrm_extension WHERE full_name=%1",
          array(1 => array($name, 'String'))) ?: $name;
      }
    }

    $this->assign('multiple', $multiple);
    $this->assign('extensions', $extensions);
    $this->assign('statuses', $statuses);
    $this->assign('friendly', $friendly);
    $this->assign('core', $this->core);
    $this->assign('snapshot', $snapshot);
    $this->assign('last_snapshot', \Civi::settings()->get('com.klangsoft_overrides:last_snapshot'));

    parent::run();
  }

  function find_overrides($ext, $base_path, $dir) {
    global $civicrm_root;

    if (!isset($this->extensions[$ext]))
      $this->extensions[$ext] = array();

    $full_path = $base_path . $dir;
    if ($dh = @opendir($full_path)) {
      while (($file = readdir($dh)) !== false) {
        if ($file[0] == '.')
          continue;
        $rel_file = "$dir/$file";
        if (is_dir("$full_path/$file"))
          $this->find_overrides($ext, $base_path, $rel_file);
        else {
          $is_core = false;
          if (!empty($this->core[$rel_file])) {
            $this->core[$rel_file]['extensions'][] = $ext;
            $is_core = true;
          }
          elseif (file_exists($civicrm_root . $rel_file)) {
            $this->core[$rel_file] = array(
              'extensions' => array($ext),
              'is_new' => false,
              'changed' => false,
              'multiple' => false
            );
            $is_core = true;
          }
          if ($is_core)
            $this->extensions[$ext][] = $rel_file;
        }
      }
      closedir($dh);
    }
  }

  function look_for_changes($save_snapshot) {
    global $civicrm_root;

    $snapshot = unserialize(CRM_Core_DAO::singleValueQuery('SELECT snapshot FROM klangsoft_overrides LIMIT 1'));
    $save_snapshot = $save_snapshot || empty($snapshot);

    foreach($this->core as $rel_file => &$params) {
      $hash = md5_file($civicrm_root . $rel_file);
      $params['is_new'] = empty($snapshot[$rel_file]);
      $params['changed'] = !$params['is_new'] && ($hash != $snapshot[$rel_file]);
      $snapshot[$rel_file] = $hash;
    }

    if ($save_snapshot) {
      foreach($this->core as &$params) {
        $params['changed'] = false;
        $params['is_new'] = false;
      }
      CRM_Core_DAO::singleValueQuery("UPDATE klangsoft_overrides SET snapshot = '" . serialize($snapshot) . "'");

      CRM_Core_BAO_Setting::setItem(date('M jS, Y, g:ia'), 'com.klangsoft_overrides', 'last_snapshot');

      CRM_Core_Session::setStatus(ts("The overrides snapshot has been saved."), ts('Saved'), 'success');
    }
  }

}
