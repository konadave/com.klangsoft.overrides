<?php

require_once 'CRM/Core/Page.php';

class CRM_Overrides_Page_Overrides extends CRM_Core_Page {

  protected $extensions = array();
  protected $core = array();

  function run() {
    CRM_Utils_System::setTitle(ts('Extension File Overrides'));

    $system = CRM_Extension_System::singleton();
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
        $core['mulitple'] = $multiple = true;
    }

    $extensions = array();
    foreach($this->extensions as $name => $files) {
      if (count($files) > 0)
        $extensions[$name] = $files;
    }

    $this->assign('multiple', $multiple);
    $this->assign('extensions', $extensions);
    $this->assign('core', $this->core);
    $this->assign('snapshot', $snapshot);

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
              'hash' => '',
              'changed' => false,
              'multiple' => false,
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
      $full_name = $civicrm_root . $rel_file;
    $hash = md5_file($full_name);
    $params['changed'] = !empty($snapshot[$rel_file]) && ($hash != $snapshot[$rel_file]['hash']);
      $params['hash'] = $snapshot[$rel_file]['hash'] = $hash;
    }

    if ($save_snapshot) {
      foreach($this->core as &$params)
        $params['changed'] = false;
      CRM_Core_DAO::singleValueQuery("UPDATE klangsoft_overrides SET snapshot = '" . serialize($snapshot) . "'");
    }
  }

}
