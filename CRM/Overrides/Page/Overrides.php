<?php

require_once 'CRM/Core/Page.php';

define('SNAPSHOT', OVERRIDE_DIR . '/snapshot.php');

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
    foreach($this->core as $core) {
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
    $this->assign('writeable', is_writable(OVERRIDE_DIR));
    $this->assign('ext_dir', OVERRIDE_DIR);

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
  						'last_check' => '',
  						'last_hash' => '',
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

  		$fn = SNAPSHOT;

  		if ($exists = file_exists(SNAPSHOT))
  			eval(file_get_contents(SNAPSHOT));
  		else
  			$snapshot = array();

  		foreach($this->core as $rel_file => &$params) {

  			if (!empty($snapshot[$rel_file])) {
  				$params['last_check'] = $snapshot[$rel_file]['last_check'];
  				$params['last_hash'] = $snapshot[$rel_file]['last_hash'];
  			}

  			$full_name = $civicrm_root . $rel_file;
  			$stat = stat($full_name);
  			$check = $stat['size'] . ':' . $stat['mtime'];

  			if ($check != $params['last_check'])
  				$hash = md5_file($full_name);
  			else
  				$hash = $params['last_hash'];

  			if ($params['last_check'])
  				$params['changed'] = ($hash != $params['last_hash']);

  			$params['last_check'] = $check;
  			$params['last_hash'] = $hash;
  		}

  		if (!$exists || $save_snapshot) {
  			foreach($this->core as &$params)
  				$params['changed'] = false;
  			file_put_contents(SNAPSHOT, '$snapshot = ' . var_export($this->core, true) . ';');
  		}
  	}

}
