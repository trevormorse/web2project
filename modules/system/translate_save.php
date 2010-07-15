<?php /* $Id$ $URL$ */
/**
 * Processes the entries in the translation form.
 * @version $Revision$
 * @author Andrew Eddie <users.sourceforge.net>
 */

if (!defined('W2P_BASE_DIR')) {
	die('You should not call this file directly.');
}

// check permissions
$perms = &$AppUI->acl();
if (!canEdit('system')) {
	$AppUI->redirect('m=public&a=access_denied');
}

$module = w2PgetParam($_POST, 'module', 0);
$lang = w2PgetParam($_POST, 'lang', $AppUI->user_locale);

$trans = w2PgetParam($_POST, 'trans', 0);
//echo '<pre>';print_r( $trans );echo '</pre>';die;

// save to core locales if a translation exists there, otherwise save
// into the module's local locale area if it exists.  If not then
// the core table is updated.
$core_filename = W2P_BASE_DIR . '/locales/' . $lang . '/' . $module . '.inc';
if (file_exists($core_filename)) {
	$filename = $core_filename;
} else {
	$mod_locale = W2P_BASE_DIR . '/modules/' . $module . '/locales';
	if (is_dir($mod_locale)) {
		if (is_dir($mod_locale . '/' . $lang)) {
			$filename = W2P_BASE_DIR . '/modules/' . $module . '/locales/' . $lang . '/' . $module . '.inc';
		} else {
			$res = mkdir($mod_locale . '/' . $lang, 0777);
			if (!$res) {
				$AppUI->setMsg("Could not create folder ($mod_locale " . '/' . "$lang) to save locale file.", UI_MSG_ERROR);
				$AppUI->redirect('m=system');
			} else {
				$filename = W2P_BASE_DIR . '/modules/' . $module . '/locales/' . $lang . '/' . $module . '.inc';
			}
		}
	} else {
		$filename = $core_filename;
	}
}

$fp = fopen($filename, 'wt');

if (!$fp) {
	$AppUI->setMsg("Could not open locales file ($filename) to save.", UI_MSG_ERROR);
	$AppUI->redirect('m=system');
}

$txt = "##\n## DO NOT MODIFY THIS FILE BY HAND!\n##\n";

if ($lang == 'en') {
	// editing the english file
	foreach ($trans as $langs) {
		if (($langs['abbrev'] || $langs['english']) && empty($langs['del'])) {
			$langs['abbrev'] = addslashes(stripslashes($langs['abbrev']));
			$langs['english'] = addslashes(stripslashes($langs['english']));
			if (!empty($langs['abbrev'])) {
				$txt .= '\'' . $langs['abbrev'] . '\'=>';
			}
			$txt .= '\'' . $langs['english'] . '\',' . "\n";
		}
	}
} else {
	// editing the translation
	foreach ($trans as $langs) {
		if (empty($langs['del'])) {
			$langs['english'] = addslashes(stripslashes($langs['english']));
			$langs['lang'] = addslashes(stripslashes($langs['lang']));
			$txt .= '\'' . $langs['english'] . '\'=>\'' . $langs['lang'] . '\',' . "\n";
		}
	}
}
fwrite($fp, $txt);
fclose($fp);

$AppUI->setMsg('Locales file saved', UI_MSG_OK);
$AppUI->redirect($AppUI->getPlace());