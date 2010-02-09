<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

##
## History module
## (c) Copyright
## J. Christopher Pereira (kripper@imatronix.cl)
## IMATRONIX
##
$AppUI->savePlace();
$titleBlock = new CTitleBlock('History', 'stock_book_blue_48.png', 'history', 'history.' . $a);
$titleBlock->show();

function show_history($history) {
	//        return $history;
	global $AppUI;
	$id = $history['history_item'];
	$module = $history['history_table'];

	$tblClassArr = getTableClass($history['history_table']);
					
	if ($tblClassArr !== false) {
		$tblObj = new $tblClassArr['className'];
		$table_id = $tblObj->_tbl_key;
	} elseif ($module == 'modules') {
		$table_id = 'mod_id';
	} else {
		$table_id = (substr($module, -1) == 's' ? substr($module, 0, -1) : $module) . '_id';
	}

	if ($module == 'login') {
		return $AppUI->_('User') . ' "' . $history['history_description'] . '" ' . $AppUI->_($history['history_action']);
	}

	if ($history['history_action'] == 'add') {
		$msg = $AppUI->_('Added new') . ' ';
	} elseif ($history['history_action'] == 'update') {
		$msg = $AppUI->_('Modified') . ' ';
	} elseif ($history['history_action'] == 'delete') {
		return $AppUI->_('Deleted') . ' "' . $history['history_description'] . '" ' . $AppUI->_('from') . ' ' . $AppUI->_($module) . ' ' . $AppUI->_('module');
	}

	$extra = '';
	
	$q = new DBQuery;
	$q->addTable($module);
	$q->addQuery($table_id);
	$q->addWhere($table_id . ' =' . $id);
	if ($q->loadResult()) {
		switch ($module) {
			case 'history':
				$link = '&a=addedit&history_id=';
				break;
			case 'files':
				$link = '&a=addedit&file_id=';
				break;
			case 'tasks':
				$link = '&a=view&task_id=';
				break;
			case 'forums':
				$link = '&a=viewer&forum_id=';
				break;
			case 'projects':
				$link = '&a=view&project_id=';
				break;
			case 'companies':
				$link = '&a=view&company_id=';
				break;
			case 'contacts':
				$link = '&a=view&contact_id=';
				break;
			case 'task_log':
				$module = 'Tasks';
				$extra = ' (Log)';
				$task_id = CTaskLog::getTaskId($id);
				$link = '&a=view&task_id='.$task_id.'&tab=1&task_log_id=';
				break;
		}
	}
	$q->clear();

	if (!empty($link)) {
		$link = '<a href="?m=' . $module . $link . $id . '">' . $history['history_description'] . '</a>';
	} 
	else {
		$link = $history['history_description'];
	}
	
	$msg .= $AppUI->_('item') . " '$link' " . $AppUI->_('in') . ' ' . $AppUI->_(ucfirst($module)) . $extra . ' ' . $AppUI->_('module'); // . $history;

	return $msg;
}

function show_project($history) {
	$name = $history['project_name'];	
	$id = $history['history_project'];
	$link = '&a=view&project_id=';
	
	if (!empty($name)) {
		$link = '<a href="?m=projects' . $link . $id . '">' . $name . '</a>';
	} 
	else {
		$link = $name;
	}
	
	return $link;
}

function show_changes($history) {
	global $AppUI;
		
	$changes = $history['history_changes'];	
	$out = '';
	
	if (!empty($changes)) {
		$s = '<table width="100%" class="tbl">';   //std or tbl
		$s .= '	<tr>';
		$s .= '		<th>'. $AppUI->_('Field') .'</th>';
		$s .= '		<th>'. $AppUI->_('Was') .'</th>';
		$s .= '		<th>'. $AppUI->_('Now') .'</th>';
		$s .= '	</tr>';
		
		$chgArr = explode("\n", $changes);
		
		foreach ($chgArr as $k) {
			$dtlArr = str_getcsv($k);

			if (!empty($dtlArr[0])) {
				$orgValue = $dtlArr[2];
				$newValue = $dtlArr[3];
				
				$tblClassArr = getTableClass($history['history_table']);
					
				if ($tblClassArr !== false) {
					$tblObj = new $tblClassArr['className'];
					$orgValue = $tblObj->resolveFieldValue($dtlArr[0], $orgValue);
					$newValue = $tblObj->resolveFieldValue($dtlArr[0], $newValue);
				}
				
				$s .= '	<tr>';
				$s .= '		<td>'. $dtlArr[0] .'</td>';
				$s .= '		<td>'. $orgValue .'</td>';
				$s .= '		<td>'. $newValue .'</td>';
				$s .= '	</tr>';
			}
		}
		
		$s .= '</table>'."\n";
		$out = $s;
	}
	
	return $out;
}

function remove_plural($str) {
	if (substr($str, -1) == 's') {
		return substr($str, 0, -1);
	}
	return $str;
}

$filter_param = w2PgetParam($_REQUEST, 'filter', ''); 
$filter = array();
$item_id = '';
if ($filter_param) {
	$in_filter = $_REQUEST['filter'];
	$filter[] = 'history_table = \'' . $_REQUEST['filter'] . '\' ';
	
	if (!empty($_REQUEST['item_id'])) {
		$filter[] = 'history_item = \'' . $_REQUEST['item_id'] . '\' ';
	}
} 
else {
	$in_filter = '';
}

if (!empty($_REQUEST['project_id'])) {
	$project_id = w2PgetParam($_REQUEST, 'project_id', 0);

	$q = new DBQuery;
	$q->addTable('tasks');
	$q->addQuery('task_id');
	$q->addWhere('task_project = ' . (int)$project_id);
	$project_tasks = implode(',', $q->loadColumn());
	if (!empty($project_tasks)) {
		$project_tasks = 'OR (history_table = \'tasks\' AND history_item IN (' . $project_tasks . '))';
	}

	$q->addTable('files');
	$q->addQuery('file_id');
	$q->addWhere('file_project = ' . (int)$project_id);
	$project_files = implode(',', $q->loadColumn());
	if (!empty($project_files)) {
		$project_files = 'OR (history_table = \'files\' AND history_item IN (' . $project_files . '))';
	}

	$filter[] = '((history_table = \'projects\' AND history_item = \'' . (int)$project_id .'\') ' . $project_tasks . ' ' . $project_files . ')';
}

$page = isset($_REQUEST['pg']) ? (int)$_REQUEST['pg'] : 1;
$limit = isset($_REQUEST['limit']) ? (int)$_REQUEST['limit'] : 100;
$offset = ($page - 1) * $limit;

if ($filter_param != '' || $page) {
	$q = new DBQuery;
	$q->addQuery('COUNT(history_id) AS hits');
	$q->addTable('history', 'h');
	$q->addWhere($filter);
	$count = intval($q->loadResult());

	$q = new DBQuery;
	$q->addQuery('history_date, history_id, history_item, history_table, history_description, history_action, history_project, history_changes');
	$q->addQuery('CONCAT(contact_first_name, \' \', contact_last_name) AS history_user_name');
	$q->addQuery('project_name');
	$q->addTable('history', 'h');
	$q->addJoin('users', 'u', 'history_user = user_id', 'left');
	$q->addJoin('contacts', 'c', 'contact_id = user_contact', 'left');
	$q->addJoin('projects', 'p', 'history_project = project_id', 'left');
	$q->addWhere($filter);
	$q->addOrder('history_date DESC');
	$q->setLimit($limit, $offset);
	$history = $q->loadList();
} else {
	$history = array();
}

$pages = (int)($count / $limit) + 1;
$max_pages = 20;

if ($pages > $max_pages) {
	$first_page = max($page - (int)($max_pages / 2), 1);
	$last_page = min($first_page + $max_pages - 1, $pages);
} else {
	$first_page = 1;
	$last_page = $pages;
}
?>

<table width="100%" cellspacing="1" cellpadding="0" border="0">
<tr>
    <td nowrap="nowrap" align="right">
<form name="filter" action="?m=history" method="post" accept-charset="utf-8">
<?php echo $AppUI->_('Changes to'); ?>:
  <select name="filter" class="text" onchange="document.filter.submit()">
   	<option value="">(<?php echo $AppUI->_('Select Filter'); ?>)</option>
    <option value="0" <?php if ($in_filter == '0') echo 'selected="selected"'; ?>><?php echo $AppUI->_('Show all'); ?></option>
    <option value="companies" <?php if ($in_filter == 'companies') echo 'selected="selected"'; ?>><?php echo $AppUI->_('Companies'); ?></option>
    <option value="projects" <?php if ($in_filter == 'projects') echo 'selected="selected"'; ?>><?php echo $AppUI->_('Projects'); ?></option>
    <option value="tasks" <?php if ($in_filter == 'tasks') echo 'selected="selected"'; ?>><?php echo $AppUI->_('Tasks'); ?></option>
    <option value="files" <?php if ($in_filter == 'files') echo 'selected="selected"'; ?>><?php echo $AppUI->_('Files'); ?></option>
    <option value="forums" <?php if ($in_filter == 'forums') echo 'selected="selected"'; ?>><?php echo $AppUI->_('Forums'); ?></option>
    <option value="login" <?php if ($in_filter == 'login') echo 'selected="selected"'; ?>><?php echo $AppUI->_('Login/Logouts'); ?></option>
  </select>
<?php
if ($pages > 1) {
	for ($i = $first_page; $i <= $last_page; $i++) {
		echo '&nbsp;';
		if ($i == $page) {
			echo '<b>' . $i . '</b>';
		} else {
			echo '<a href="?m=history&filter=' . $in_filter . '&pg=' . $i . '">' . $i . '</a>';
		}
	}
}
?>
</form>
        </td>
	<td align="right"><input class="button" type="button" value="<?php echo $AppUI->_('Add history'); ?>" onclick="window.location='?m=history&a=addedit'"></td>
</table>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th width="10">&nbsp;</th>
	<th width="150"><?php echo $AppUI->_('Date'); ?></th>
	<th width="150"><?php echo $AppUI->_('Project'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Description'); ?></th>
	<th ><?php echo $AppUI->_('Changes'); ?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('User'); ?>&nbsp;&nbsp;</th>
</tr>
<?php
foreach ($history as $row) {
	$module = $row['history_table'] == 'task_log' ? 'tasks' : $row['history_table'];
	// Checking permissions.
	// TODO: Enable the lines below to activate new permissions.
	$perms = &$AppUI->acl();
	//The next line makes no sense and takes loads of time
	//if ($module == 'login' || $perms->checkModuleItem($module, "access", $row['history_item']))  {
	$df = $AppUI->getPref('SHDATEFORMAT');
	$tf = $AppUI->getPref('TIMEFORMAT');

	$hd = new Date($row['history_date']);
?>
<tr>	
	<td align="center"><a href='<?php echo '?m=history&a=addedit&history_id=' . $row['history_id'] ?>'><img src="<?php echo w2PfindImage('icons/pencil.gif'); ?>" alt="<?php echo $AppUI->_('Edit History') ?>" border="0" width="12" height="12" /></a></td>
	<td align="center"><?php echo $hd->format($df) . ' ' . $hd->format($tf); ?></td>
	<td><?php echo show_project($row) ?></td>	
	<td><?php echo show_history($row) ?></td>	
	<td><?php echo show_changes($row) ?></td>	
	<td align="left"><?php echo $row['history_user_name'] ?></td>
</tr>	
<?php
}
?>
</table>