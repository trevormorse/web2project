<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $task_id, $df, $canEdit, $m;

$perms = &$AppUI->acl();
if (!$perms->checkModule('task_log', 'view')) {
	$AppUI->redirect('m=public&a=access_denied');
}

$problem = intval(w2PgetParam($_GET, 'problem', null));
// get sysvals
$taskLogReference = w2PgetSysVal('TaskLogReference');
$taskLogReferenceImage = w2PgetSysVal('TaskLogReferenceImage');
?>
<script language="JavaScript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
$canDelete = $perms->checkModule('task_log', 'delete');
if ($canDelete) {
?>
function delIt2(id) {
	if (confirm( '<?php echo $AppUI->_('doDelete', UI_OUTPUT_JS) . ' ' . $AppUI->_('Task Log', UI_OUTPUT_JS) . '?'; ?>' )) {
		document.frmDelete2.task_log_id.value = id;
		document.frmDelete2.submit();
	}
}
<?php } ?>
</script>

<table border="0" cellpadding="2" cellspacing="1" width="100%" class="tbl">
<form name="frmDelete2" action="./index.php?m=tasks" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_updatetask" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="task_log_id" value="0" />
</form>

<tr>
	<th></th>
	<th><?php echo $AppUI->_('Date'); ?></th>
        <th title="<?php echo $AppUI->_('Reference'); ?>"><?php echo $AppUI->_('Ref'); ?></th>
	<th width="100"><?php echo $AppUI->_('Summary'); ?></th>
    <th><?php echo $AppUI->_('URL'); ?></th>
	<th width="100"><?php echo $AppUI->_('User'); ?></th>
	<th width="100"><?php echo $AppUI->_('Hours'); ?></th>
	<th width="100" nowrap="nowrap"><?php echo $AppUI->_('Cost Code'); ?></th>
	<th width="100%"><?php echo $AppUI->_('Comments'); ?></th>
	<th></th>
</tr>
<?php
// Pull the task comments
$q = new DBQuery();
$q->addTable('task_log');
$q->addQuery('task_log.*, user_username, billingcode_name as task_log_costcode');
$q->addQuery('CONCAT(contact_first_name, \' \', contact_last_name) AS real_name');
$q->addWhere('task_log_task = ' . (int)$task_id . ($problem ? ' AND task_log_problem > 0' : ''));
$q->addOrder('task_log_date');
$q->leftJoin('billingcode', '', 'task_log.task_log_costcode = billingcode_id');
$q->addJoin('users', '', 'task_log_creator = user_id', 'inner');
$q->addJoin('contacts', 'ct', 'contact_id = user_contact', 'inner');
$logs = $q->loadList();

$s = '';
$hrs = 0;
$canEdit = $perms->checkModule('task_log', 'edit');
foreach ($logs as $row) {
	$task_log_date = intval($row['task_log_date']) ? new CDate($row['task_log_date']) : null;
	$style = $row['task_log_problem'] ? 'background-color:#cc6666;color:#ffffff' : '';

	$s .= '<tr bgcolor="white" valign="top"><td>';
	if ($canEdit) {
		if ($tab == -1) {
			$s .= '<a href="?m=tasks&a=view&task_id=' . $task_id . '&tab=' . $AppUI->getState('TaskLogVwTab');
		} else {
			$s .= '<a href="?m=tasks&a=view&task_id=' . $task_id . '&tab=1';

		}
		$s .= '&task_log_id=' . $row['task_log_id'] . '#log">' . w2PshowImage('icons/stock_edit-16.png', 16, 16, '') . '</a>';
	}
	$s .= '</td><td nowrap="nowrap">' . ($task_log_date ? $task_log_date->format($df) : '-') . '</td>';
	//$s .= '<td align="center" valign="middle">'.($row['task_log_problem'] ?  w2PshowImage('icons/mark-as-important-16.png', 16, 16, 'Problem', 'Problem' ) : '').'</td>';
	$reference_image = '-';
	if ($row['task_log_reference'] > 0) {
		if (isset($taskLogReferenceImage[$row['task_log_reference']])) {
			$reference_image = w2PshowImage($taskLogReferenceImage[$row['task_log_reference']], 16, 16, $taskLogReference[$row['task_log_reference']], $taskLogReference[$row['task_log_reference']]);
		} elseif (isset($taskLogReference[$row['task_log_reference']])) {
			$reference_image = $taskLogReference[$row['task_log_reference']];
		}
	}
	$s .= '<td align="center" valign="middle">' . $reference_image . '</td>';
	$s .= '<td width="30%" style="' . $style . '">' . $row['task_log_name'] . '</td>';
	$s .= !empty($row['task_log_related_url']) ? '<td><a href="' . $row['task_log_related_url'] . '" title="' . $row['task_log_related_url'] . '">' . $AppUI->_('URL') . '</a></td>' : '<td></td>';
	$s .= '<td width="100">' . $row['real_name'] . '</td>';
	$s .= '<td width="100" align="right">' . sprintf('%.2f', $row['task_log_hours']) . '<br />(';
	$minutes = (int)(($row['task_log_hours'] - ((int)$row['task_log_hours'])) * 60);
	$minutes = ((strlen($minutes) == 1) ? ('0' . $minutes) : $minutes);
	$s .= (int)$row['task_log_hours'] . ':' . $minutes . ')</td>';
	$s .= '<td width="100">' . $row['task_log_costcode'] . '</td><td>' . '<a name="tasklog' . $row['task_log_id'] . '"></a>';

	// dylan_cuthbert: auto-transation system in-progress, leave these lines
	$transbrk = "\n[translation]\n";
	$descrip = str_replace("\n", '<br />', ($row['task_log_description']));
	$tranpos = strpos($descrip, str_replace("\n", '<br />', $transbrk));
	if ($tranpos === false) {
		$s .= $descrip;
	} else {
		$descrip = substr($descrip, 0, $tranpos);
		$tranpos = strpos($row['task_log_description'], $transbrk);
		$transla = substr($row['task_log_description'], $tranpos + strlen($transbrk));
		$transla = trim(str_replace("'", '"', $transla));
		$s .= $descrip . '<div style="font-weight: bold; text-align: right"><a title="' . $transla . '" class="hilite">[' . $AppUI->_('translation') . ']</a></div>';
	}
	// end auto-translation code

	$s .= '</td><td>';
	if ($canDelete) {
		$s .= '<a href="javascript:delIt2(' . $row['task_log_id'] . ');" title="' . $AppUI->_('delete log') . '">' . w2PshowImage('icons/stock_delete-16.png', 16, 16, '') . '</a>';
	}
	$s .= '</td></tr>';
	$hrs += (float)$row['task_log_hours'];
}
$s .= '<tr bgcolor="white" valign="top">';
$s .= '<td colspan="6" align="right">' . $AppUI->_('Total Hours') . ' =</td>';
$s .= '<td align="right">' . sprintf('%.2f', $hrs) . '</td>';
$s .= '<td align="right" colspan="3"><form action="?m=tasks&a=view&tab=1&task_id=' . $task_id . '" method="post" accept-charset="utf-8">';
if ($perms->checkModuleItem('tasks', 'edit', $task_id)) {
	$s .= '<input type="submit" class="button" value="' . $AppUI->_('new log') . '"></form></td>';
}
$s .= '</tr>';
echo $s;
?>
</table>
<table>
<tr>
	<td><?php echo $AppUI->_('Key'); ?>:</td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#ffffff">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Normal Log'); ?></td>
	<td bgcolor="#CC6666">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Problem Report'); ?></td>
</tr>
</table>