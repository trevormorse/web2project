<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

##
##	Companies: View User sub-table
##

global $AppUI, $company;

$contacts = CCompany::getContacts($AppUI, $company->company_id);

?><table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl"><?php
if (count($contacts) > 0) {
	?>
	<tr>
		<th><?php echo $AppUI->_('Name'); ?></th>
		<th><?php echo $AppUI->_('Job Title'); ?></th>
		<th><?php echo $AppUI->_('e-mail'); ?></th>
		<th><?php echo $AppUI->_('Phone'); ?></th>
		<th><?php echo $AppUI->_('Department'); ?></th>
	</tr>
	<?php
    $contact = new CContact();
	foreach ($contacts as $contact_id => $contact_data) {
		$contact->contact_id = $contact_id;
        $info = $contact->getContactMethods(array('email_primary', 'phone_primary'));

        echo '<tr><td class="hilite">';
		echo '<a href="./index.php?m=contacts&a=view&contact_id=' . $contact_data['contact_id'] . '">'; 
		echo $contact_data['contact_first_name'] . ' ' . $contact_data['contact_last_name'];
		echo '</a>';
		echo '</td>';
		echo '<td class="hilite">' . $contact_data['contact_job'] . '</td>';
		echo '<td class="hilite"><a href="mailto:' . $info['email_primary'] . '">' . $info['email_primary'] . '</a></td>';
		echo '<td class="hilite">' . $info['phone_primary'] . '</td>';
		echo '<td class="hilite">' . $contact_data['dept_name'] . '</td>';
		echo '</tr>';
	}
} else {
	?><tr><td colspan="5"><?php echo $AppUI->_('No data available') . '<br />' . $AppUI->getMsg(); ?></td></tr><?php
}
?>

	<tr>
		<td colspan="5" align="right" valign="top" style="background-color:#ffffff">
			<input type="button" class=button value="<?php echo $AppUI->_('new contact') ?>" onClick="javascript:window.location='./index.php?m=contacts&a=addedit&company_id=<?php echo $company->company_id; ?>'">
		</td>
	</tr>
</table>