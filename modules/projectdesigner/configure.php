<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
/* This file will write a php config file to be included during execution of
* all Project designer files which require the configuration options. */
global $m;

// Deny all but system admins
if (getDenyEdit('system')) {
	$AppUI->redirect('m=public&a=access_denied');
}

$AppUI->savePlace();
$config = new CProjectDesignerConfigure($AppUI);

//if this is a submitted page, overwrite the config file.
if (w2PgetParam($_POST, 'Save', '') != '') {
	$ok = $config->saveConfig($_POST);
	if (!$ok) {
		exit;
	}
} 
elseif (w2PgetParam($_POST, $AppUI->_('Cancel'), '') != '') {
	$AppUI->redirect('m=system&a=viewmods');
}
else {
	include($config->getConfigFile());
	$config->loadConfig($PROJDESIGN_CONFIG);
}

// setup the title block
$titleBlock = new CTitleBlock('Project Designer Module Configuration', 'projectdesigner.png', $m,  $m . '.' . $a);
$titleBlock->addCrumb('?m=system', 'System Admin');
$titleBlock->addCrumb('?m=system&a=viewmods', 'Modules');
$titleBlock->show();

$config->outputForm();