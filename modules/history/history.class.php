<?php

class CHistory extends CW2pObject {
  public $history_id = null;
  public $history_date = null;
  public $history_user = null;
  public $history_action = null;
  public $history_item = null;
  public $history_table = null;
  public $history_project = null;
  public $history_name = null;
  public $history_changes = null;
  public $history_description = null;

  public function __construct() {
    parent::__construct('history', 'history_id');
  }

  public function check() {
    // ensure the integrity of some variables
    $errorArray = array();
    $baseErrorMsg = get_class($this) . '::store-check failed - ';
    //there aren't any checks yet

	  return $errorArray;
  }

	public function delete(CAppUI $AppUI = null) {
    global $AppUI;
    $perms = $AppUI->acl();

    return true;
  }

	public function store(CAppUI $AppUI = null) {
    global $AppUI;
    $perms = $AppUI->acl();

    return true;
  }

  public static function addHistory($table, $id, $project_id = 0, $action = 'modify', $description = '', $changes = '') {
		global $AppUI;
		$q = new DBQuery;
		
		$description = str_replace("'", "\'", $description);
		$changes = str_replace("'", "\'", $changes);
		
		$q->clear();
		$q->addTable('history');
		$q->addInsert('history_action', $action);
		$q->addInsert('history_item', $id);
		$q->addInsert('history_description', $description);
		$q->addInsert('history_changes', $changes);
		$q->addInsert('history_user', $AppUI->user_id);
		$q->addInsert('history_date', $q->dbfnNow(), false, true);
		$q->addInsert('history_project', $project_id);
		$q->addInsert('history_table', $table);
		$q->exec();
		echo db_error();
		$q->clear();
  }
}