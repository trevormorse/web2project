<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage modules
 *	@version $Revision$
 */

if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/**
 *	CW2pObject Abstract Class.
 *
 *	Parent class to all database table derived objects
 *	@author Andrew Eddie <eddieajau@users.sourceforge.net>
 *	@abstract
 */
class CW2pObject {
	/**
	 *	@var string Name of the table prefix in the db schema
	 */
	public $_tbl_prefix = '';
	/**
	 *	@var string Name of the table in the db schema relating to child class
	 */
	public $_tbl = '';
	/**
	 *	@var string Name of the primary key field in the table
	 */
	public $_tbl_key = '';
	
	/**
	 *	@var string Name of the primary key field in the table
	 */
	public $_tbl_metadata = '';
	
	/**
	 *	@var string Error message
	 */
	public $_error = '';

	/**
	 * @var object Query Handler
	 */
	public $_query;

	/**
	 *	Object constructor to set table and key field
	 *
	 *	Can be overloaded/supplemented by the child class
	 *	@param string $table name of the table in the db schema relating to child class
	 *	@param string $key name of the primary key field in the table
	 */
	public function __construct($table, $key, $hasMetadata = false) {
		$this->_tbl = $table;
		$this->_tbl_key = $key;
		$this->_tbl_metadata = $hasMetadata;
		$this->_tbl_prefix = w2PgetConfig('dbprefix', '');
		$this->_query = new DBQuery;
	}
	
	/**
	 *	@return string Returns the error message
	 */
	public function getError() {
		return $this->_error;
	}
	
	public function __set($name, $value) {
  	if ($this->_tbl_metadata and substr($name, 0, 2) != '__') {  //avoid looking if already expanded
  		$fname = '__'.$name;
  		if (isset($this->{$fname})) {
  			$this->{$fname}['value'] = $value;
    		return;
  		}
  	}
   	$this->$name = $value;
  }
	
	public function __get($name) {
  	if ($this->_tbl_metadata and substr($name, 0, 2) != '__') {  //avoid looking if already expanded
  		$fname = '__'.$name;
  		if (isset($this->{$fname})) {
  			return $this->{$fname}['value'];
  		}
  	}

  	if (substr($name, 0, 1) == '#') {  //represents a dynamic function call
  		$fctnName = substr($name, 1);
  		return call_user_func(array($this, $fctnName));
  	}
  	
		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __get(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE
		);
		return null;
  }	
	
	public function __isset($name) {
  	if ($this->_tbl_metadata and substr($name, 0, 2) != '__') {  //avoid looking if already expanded
  		$fname = '__'.$name;
  		if ($name == 'contact_birthday') {
  			$x=1;
  		}
  		return isset($this->{$fname}['value']);
  	}
		return false;		
	}
  
	public function __unset($name) {
  	if ($this->_tbl_metadata and substr($name, 0, 2) != '__') {  //avoid looking if already expanded
  		$fname = '__'.$name;
  		unset($this->{$fname}['value']);
  	}
	}
	
	public function getFieldInfo($name, $key) {
  	if ($this->_tbl_metadata and substr($name, 0, 2) != '__') {  //avoid looking if already expanded
  		$fname = '__'.$name;
  		if (isset($this->{$fname})) {
  			if (isset($this->{$fname}[$key])) {
  				return $this->{$fname}[$key];
  			}
  		}
  	}
		return null;
	}
	
	public function setFieldInfo($name, $key, $value) {
  	if ($this->_tbl_metadata and substr($name, 0, 2) != '__') {  //avoid looking if already expanded
  		$fname = '__'.$name;
  		if (isset($this->{$fname})) {
    		$this->{$fname}[$key] = $value;
  		}
  	}
	}
	
	public function getFieldVars() {
		$fld_vars = array();
		
		if ($this->_tbl_metadata) {
			foreach (get_object_vars($this) as $k => $v) {
				if (is_array($v) and $k[0] == '_' and $k[1] == '_') { //indicates a field property
					$fld_vars[substr($k, 2)] = $v['value'];
				}
			}
		}
		else {
			foreach (get_object_vars($this) as $k => $v) {
				if (is_array($v) or is_object($v) or $k[0] == '_') { // internal or NA field
					continue;
				}
				
				$fld_vars[$k] = $v;
			}
		}
		return $fld_vars;
	}
	
	/**
	 *	Binds a named array/hash to this object
	 *
	 *	can be overloaded/supplemented by the child class
	 *	@param array $hash named array
	 *  @param $prefix Defaults to null, prefix to use with hash keys
	 *  @param $checkSlashes Defaults to true, strip any slashes from the hash values
	 *  @param $bindAll Bind all values regardless of their existance as defined instance variables
	 *	@return null|string	null is operation was satisfactory, otherwise returns an error
	 */
	public function bind($hash, $prefix = null, $checkSlashes = true, $bindAll = false) {
		if (!is_array($hash)) {
			$this->_error = get_class($this) . '::bind failed.';
			return false;
		} else {
			/*
			* We need to filter out any object values from the array/hash so the bindHashToObject()
			* doesn't die. We also avoid issues such as passing objects to non-object functions
			* and copying object references instead of cloning objects. Object cloning (if needed)
			* should be handled seperatly anyway.
			*/
			foreach ($hash as $k => $v) {
				if (!(is_object($hash[$k]))) {
                    $filtered_hash[$k] = (is_string($v)) ? strip_tags($v) : $v;
				}
			}
			$this->_query->bindHashToObject($filtered_hash, $this, $prefix, $checkSlashes, $bindAll);
			$this->_query->clear();
			return true;
		}
	}

	/**
	 *	Binds an array/hash to this object
	 *	@param int $oid optional argument, if not specifed then the value of current key is used
	 *	@return any result from the database operation
	 */
	public function load($oid = null, $strip = true) {
		return $this->loadBase($oid, $strip);
	}
	
	public function loadBase($oid = null, $strip = true) {
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval($oid);
		}
		$oid = $this->$k;
		if ($oid === null) {
			return false;
		}
		
		$this->_query->clear();
		$this->_query->addTable($this->_tbl);
		$this->_query->addWhere($this->_tbl_key . ' = ' . $oid);
		$hash = $this->_query->loadHash();
		
		//If no record was found send false because there is no data
		if (!$hash) {
			return false;
		}
		
		$this->_query->bindHashToObject($hash, $this, null, $strip);
		$this->_query->clear();
		return $this;
	}

	/**
	 *	Returns an array, keyed by the key field, of all elements that meet
	 *	the where clause provided. Ordered by $order key.
	 */
	public function loadAll($order = null, $where = null) {
		$this->_query->clear();
		$this->_query->addTable($this->_tbl);
		if ($order) {
			$this->_query->addOrder($order);
		}
		if ($where) {
			$this->_query->addWhere($where);
		}
		$result = $this->_query->loadHashList($this->_tbl_key);
		$this->_query->clear();
		return $result;
	}

	/**
	 *	Return a DBQuery object seeded with the table name.
	 *	@param string $alias optional alias for table queries.
	 *	@return DBQuery object
	 */
	public function &getQuery($alias = null) {
		$this->_query->clear();
		$this->_query->addTable($this->_tbl, $alias);
		return $this->_query;
	}

	/**
	 *	Generic check method
	 *
	 *	Can be overloaded/supplemented by the child class
	 *	@return null if the object is ok
	 */
	public function check() {
		return null;
	}

	/**
	 *	Clone the current record
	 *
	 *	@author	handco <handco@users.sourceforge.net>
	 *	@return	object	The new record object or null if error
	 **/
	public function duplicate() {
		$_key = $this->_tbl_key;

		// In php4 assignment does a shallow copy
		// in php5 clone is required
		if (version_compare(phpversion(), '5') >= 0) {
			$newObj = clone($this);
		} else {
			$newObj = $this;
		}
		// blanking the primary key to ensure that's a new record
		$newObj->$_key = '';

		return $newObj;
	}

	/**
	 *	Default trimming method for class variables of type string
	 *
	 *	@param object Object to trim class variables for
	 *	Can be overloaded/supplemented by the child class
	 *	@return none
	 */
	public function w2PTrimAll() {
		$trim_arr = $this->getFieldVars();

		foreach ($trim_arr as $trim_key => $trim_val) {
			if (!(strcasecmp(gettype($trim_val), 'string'))) {
				$this->{$trim_key} = trim($trim_val);
			}
		}
	}

	/**
	 *	Inserts a new row if id is zero or updates an existing row in the database table
	 *
	 *	Can be overloaded/supplemented by the child class
	 *	@return null|string null if successful otherwise returns an error message
	 */
	public function store($updateNulls = false, $updateHistory = true) {
		global $AppUI;

		$this->w2PTrimAll();
		$msg = $this->check();
		
		if ($msg) {
			return get_class($this) . '::store-check failed ' . $msg;
		}
		$k = $this->_tbl_key;
		
		if ($this->$k) {
			$store_type = 'update';
			
			if ($this->_tbl_metadata) { 
				$className = get_class($this);
				$orgObj = new $className;
				$orgObj->loadBase($this->getFieldInfo($this->_tbl_key, 'value'), false);
			}
			else {
				$orgObj = null;
			}
			
			$q = new DBQuery;
			$ret = $q->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
			$q->clear();
		} else {
			$store_type = 'add';
			$q = new DBQuery;
			$ret = $q->insertObject($this->_tbl, $this, $this->_tbl_key);
			$q->clear();
		}

		if ($ret && $updateHistory) {
			// only record history if an update or insert actually occurs.
			if ($store_type == 'add') {
				addHistory($this->_tbl, $this->$k, $store_type, $this->getHistoryDescription($store_type), $this->getProjectId());
			}
			else {
				$this->addUpdateHistory($orgObj, $this->getHistoryDescription($store_type), $this->getProjectId());
			}
		}

		return ((!$ret) ? (get_class($this) . '::store failed ' . db_error()) : null);
	}
	
	protected function getProjectId() {
		return 0;
	}

	protected function getHistoryDescription($store_type) {
		global $AppUI;
		
		$s = '';
		$s .= $AppUI->_('ACTION') . ': ' . $store_type . ' ' . $AppUI->_('TABLE') . ': ' . $this->_tbl . ' ' . $AppUI->_('ID') . ': ' . $this->_tbl_key; 
		return $s;
	}
	
	/**
	 *	Generic check for whether dependencies exist for this object in the db schema
	 *
	 *	Can be overloaded/supplemented by the child class
	 *	@param string $msg Error message returned
	 *	@param int Optional key index
	 *	@param array Optional array to compiles standard joins: format [label=>'Label',name=>'table name',idfield=>'field',joinfield=>'field']
	 *	@return true|false
	 */
	public function canDelete(&$msg, $oid = null, $joins = null) {
		global $AppUI;

		// First things first.  Are we allowed to delete?
		$acl = &$AppUI->acl();
		if (!$acl->checkModuleItem($this->_tbl, 'delete', $oid)) {
			$msg = $AppUI->_('noDeletePermission');
			return false;
		}

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval($oid);
		}
		if (is_array($joins)) {
			$select = $k;
			$join = '';

			$q = new DBQuery;
			$q->addTable($this->_tbl);
			$q->addWhere($k . ' = \'' . $this->$k . '\'');
			$q->addGroup($k);
			foreach ($joins as $table) {
				$q->addQuery('COUNT(DISTINCT ' . $table['idfield'] . ') AS ' . $table['idfield']);
				$q->addJoin($table['name'], $table['name'], $table['joinfield'] . ' = ' . $k);
			}
			$obj = null;
			$q->loadObject($obj);
			$q->clear();

			if (!$obj) {
				$msg = db_error();
				return false;
			}
			$msg = array();
			foreach ($joins as $table) {
				$k = $table['idfield'];
				if ($obj->$k) {
					$msg[] = $AppUI->_($table['label']);
				}
			}

			if (count($msg)) {
				$msg = $AppUI->_('noDeleteRecord') . ': ' . implode(', ', $msg);
				return false;
			} else {
				return true;
			}
		}

		return true;
	}

	/**
	 *	Default delete method
	 *
	 *	Can be overloaded/supplemented by the child class
	 *	@return null|string null if successful otherwise returns and error message
	 */
	public function delete($oid = null) {
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval($oid);
		}
		if (!$this->canDelete($msg)) {
			return $msg;
		}

		$q = new DBQuery;
		$q->setDelete($this->_tbl);
		$q->addWhere($this->_tbl_key . ' = \'' . $this->$k . '\'');
		$result = ((!$q->exec()) ? db_error() : null);
		
		if (!$result) {
			// only record history if deletion actually occurred
			addHistory($this->_tbl, $this->$k, 'delete', $this->getHistoryDescription($store_type));
		}
		$q->clear();
		return $result;
	}

	/**
	 *	Get specifically denied records from a table/module based on a user
	 *	@param int User id number
	 *	@return array
	 */
	public function getDeniedRecords($uid) {
		$uid = intval($uid);
		$uid || exit('FATAL ERROR ' . get_class($this) . '::getDeniedRecords failed, user id = 0');

		$perms = &$GLOBALS['AppUI']->acl();
		return $perms->getDeniedItems($this->_tbl, $uid);
	}

	/**
	 *	Returns a list of records exposed to the user
	 *	@param int User id number
	 *	@param string Optional fields to be returned by the query, default is all
	 *	@param string Optional sort order for the query
	 *	@param string Optional name of field to index the returned array
	 *	@param array Optional array of additional sql parameters (from and where supported)
	 *	@return array
	 */
	// returns a list of records exposed to the user
	public function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null, $table_alias = '') {
		$perms = &$GLOBALS['AppUI']->acl();
		$uid = intval($uid);
		$uid || exit('FATAL ERROR ' . get_class($this) . '::getAllowedRecords failed');
		$deny = &$perms->getDeniedItems($this->_tbl, $uid);
		$allow = &$perms->getAllowedItems($this->_tbl, $uid);
		/*print_r('Deny:');
		print_r($deny);
		print_r('Allow:');
		print_r($allow);*/
		//if (! $perms->checkModule($this->_tbl, 'view', $uid )) {
		//  if (! count($allow))
		//    return array();	// No access, and no allow overrides, so nothing to show.
		//} else {
		//  $allow = array();	// Full access, allow overrides don't mean anything.
		//}
		$this->_query->clear();
		$this->_query->addQuery($fields);
		$this->_query->addTable($this->_tbl);

		if (isset($extra['from'])) {
			$this->_query->addTable($extra['from']);
		}

		if (isset($extra['join']) && isset($extra['on'])) {
			$this->_query->addJoin($extra['join'], $extra['join'], $extra['on']);
		}

		if (count($allow)) {
			if ((array_search('0', $allow)) === false) {
				//If 0 (All Items of a module) are not permited then just add the allowed items only
				$this->_query->addWhere(($table_alias ? $table_alias . '.' : '') . $this->_tbl_key . ' IN (' . implode(',', $allow) . ')');
			} else {
				//If 0 (All Items of a module) are permited then don't add a where clause so the user is permitted to see all
			}
			//Denials are only required if we were able to see anything in the first place so now we handle the denials
			if (count($deny)) {
				if ((array_search('0', $deny)) === false) {
					//If 0 (All Items of a module) are not on the denial array then just deny the denied items
					$this->_query->addWhere(($table_alias ? $table_alias . '.' : '') . $this->_tbl_key . ' NOT IN (' . implode(',', $deny) . ')');
				} elseif ((array_search('0', $allow)) === false) {
					//If 0 (All Items of a module) are denied and we have granted some then implicit denial to everything else is already in place
				} else {
					//if we allow everything and deny everything then denials have higher priority... Deny Everything!
					$this->_query->addWhere('0=1');
				}
			}
		} else {
			//if there are no allowances, deny!
			$this->_query->addWhere('0=1');
		}

		if (isset($extra['where'])) {
			$this->_query->addWhere($extra['where']);
		}

		if ($orderby) {
			$this->_query->addOrder($orderby);
		}
		//print_r($this->_query->prepare());
		return $this->_query->loadHashList($index);
	}

	public function getAllowedSQL($uid, $index = null) {
		$perms = &$GLOBALS['AppUI']->acl();
		$uid = intval($uid);
		$uid || exit('FATAL ERROR ' . get_class($this) . '::getAllowedSQL failed');
		$deny = &$perms->getDeniedItems($this->_tbl, $uid);
		$allow = &$perms->getAllowedItems($this->_tbl, $uid);
		/*		print_r('allow:');
		print_r($allow);
		print_r('deny:');
		print_r($deny);
		print_r('deny:');
		print_r($deny);
		if (! $perms->checkModule($this->_tbl, 'view', $uid )) {
		if (! count($allow))
		return array('1=0');*/ // No access, and no allow overrides, so nothing to show.
		//} else {
		//  $allow = array();	// Full access, allow overrides don't mean anything.
		//}

		if (!isset($index)) {
			$index = $this->_tbl_key;
		}
		$where = array();
		if (count($allow)) {
			if ((array_search('0', $allow)) === false) {
				//If 0 (All Items of a module) are not permited then just add the allowed items only
				$where[] = $index  . ' IN (' . implode(',', $allow) . ')';
			} else {
				//If 0 (All Items of a module) are permited then don't add a where clause so the user is permitted to see all
			}
			//Denials are only required if we were able to see anything in the first place so now we handle the denials
			if (count($deny)) {
				if ((array_search('0', $deny)) === false) {
					//If 0 (All Items of a module) are not on the denial array then just deny the denied items
					$where[] = $index . ' NOT IN (' . implode(',', $deny) . ')';
				} elseif ((array_search('0', $allow)) === false) {
					//If 0 (All Items of a module) are denied and we have granted some then implicit denial to everything else is already in place
				} else {
					//if we allow everything and deny everything then denials have higher priority... Deny Everything!
					$where[] = '0=1';
				}
			}
		} else {
			//if there are no allowances, deny!
			$where[] = '0=1';
		}
		return $where;
	}

	public function setAllowedSQL($uid, &$query, $index = null, $key = null) {
		$perms = &$GLOBALS['AppUI']->acl();
		$uid = intval($uid);
		$uid || exit('FATAL ERROR ' . get_class($this) . '::getAllowedSQL failed');
		$deny = &$perms->getDeniedItems($this->_tbl, $uid);
		$allow = &$perms->getAllowedItems($this->_tbl, $uid);
		// Make sure that we add the table otherwise dependencies break
		if (isset($index)) {
			if (!$key) {
				$key = substr($this->_tbl, 0, 2);
			}
			$query->leftJoin($this->_tbl, $key, $key . '.' . $this->_tbl_key . ' = ' . $index);
		}

		if (count($allow)) {
			if ((array_search('0', $allow)) === false) {
				//If 0 (All Items of a module) are not permited then just add the allowed items only
				$query->addWhere(((!$key) ? '' : $key . '.') . $this->_tbl_key . ' IN (' . implode(',', $allow) . ')');
			} else {
				//If 0 (All Items of a module) are permited then don't add a where clause so the user is permitted to see all
			}
			//Denials are only required if we were able to see anything in the first place so now we handle the denials
			if (count($deny)) {
				if ((array_search('0', $deny)) === false) {
					//If 0 (All Items of a module) are not on the denial array then just deny the denied items
					$query->addWhere(((!$key) ? '' : $key . '.') . $this->_tbl_key . ' NOT IN (' . implode(',', $deny) . ')');
				} elseif ((array_search('0', $allow)) === false) {
					//If 0 (All Items of a module) are denied and we have granted some then implicit denial to everything else is already in place
				} else {
					//if we allow everything and deny everything then denials have higher priority... Deny Everything!
					$query->addWhere('0=1');
				}
			}
		} else {
			//if there are no allowances, deny!
			$query->addWhere('0=1');
		}
	}

	/*
	* Decode HTML entities in object vars
	*/
	public function htmlDecode() {
		foreach ($this->getFieldVars() as $k => $v) {
			if ($v == null) {
				continue;
			}
			$this->$k = htmlspecialchars_decode($v);
		}
	}

	protected function completeFieldTypes() {
		$fldArr = $this->_query->MetaColumns($this->_tbl);			
		
		foreach ($this->getFieldVars() as $k => $v) {
			if (!$this->getFieldInfo($k, 'type')) {
				$fldTypeInfo = $fldArr[strtoupper($k)];
				$this->setFieldInfo($k, 'type', $fldTypeInfo->type . '(' . $fldTypeInfo->max_length . ')');
			}
		}
	}
	
	protected function addUpdateHistory($orgObj, $description, $project_id = 0) {
		if (!w2PgetConfig('log_changes')) {
			return;
		}

		if (!CAppUI::isActiveModule('History')) {
			$AppUI->setMsg('History module is not loaded, but your config file has requested that changes be logged.  You must either change the config file or install and activate the history module to log changes.', UI_MSG_ALERT);
			$q->clear();
			return;
		}

		$changes = '';
		
		if ($this->_tbl_metadata) { 
			//figure out the changes
			$chgArr = array();
			foreach ($this->getFieldVars() as $k => $v) {
				if ($this->getFieldInfo($k, 'tracked')) {
					if ($this->isChanged($k, $orgObj)) {
						$chgArr[$k] = array(
							'type' => $this->getFieldInfo($k, 'type'),
							'orgValue' => $orgObj->{$k},
							'newValue' => $this->{$k},
						);  	
					}	
				}
			}
	
			foreach ($chgArr as $k => $v) {
				$changes .= '"' . $k . '","' . $v['type'] . '","' .  strtr($v['orgValue'], '"', '""') . '","' . strtr($v['newValue'], '"', '""') . '"' . "\n";
			}
		}
				
		if (!$this->_tbl_metadata or $changes != '') {
			CHistory::addHistory($this->_tbl, $this->{$this->_tbl_key}, $project_id , 'update', $description, $changes);
		}
	}
	
	private function isChanged($k, &$orgObj) {
		if (htmlspecialchars_decode($this->{$k}) == htmlspecialchars_decode($orgObj->{$k})) {
			return false;
		}
		
		$type = $this->getFieldInfo($k, 'type');
		
		if (substr($type, 0, 8) == 'numeric(') {
			$matches = array();
			preg_match('{numeric\((?P<length>\d+),(?P<prec>\d+)\)}', $type, $matches);

			if (isset($matches['prec'])) {
				$prec = $matches['prec'];
				if (round($this->{$k}, $prec) == round($orgObj->{$k}, $prec)) {
					return false;
				}
			}
		}
		else if (substr($type, 0, 9) == 'datetime(') {
			if ($this->emptyDate($this->{$k}) and $this->emptyDate($orgObj->{$k})) {
				return false;
			}
		}
		
		return true;
	}
	
	private function emptyDate($str) {
		if (empty($str) or $str == '0000-00-00 00:00:00') {
			return true;
		}
		return false;
	}
	
	public function resolveFieldValue($fname, $baseValue) {
		global $AppUI;
		
		$value = $baseValue;
		$fldType = $this->getFieldInfo($fname, 'type');
		
		if ($fldType == 'select') {
			$list = $this->getFieldInfo($fname, 'list');
			$value = array_key_exists($value, $list) ? $list[$value] : $value;
		}
		else if ($fldType == 'table_select') {
			$tbl = $this->getFieldInfo($fname, 'select_table');
			$fld = $this->getFieldInfo($fname, 'select_field');
			$tblClassArr = getTableClass($tbl);
			
			if ($tblClassArr !== false) {
				$tblObj = new $tblClassArr['className'];
				$tblObj->loadBase($value, false);
				$value = $tblObj->{$fld};
			}
		}
		else if ($fldType == 'checkbox') {
			$value = (empty($value) or $value == '0') ? $AppUI->_('unselected') : $AppUI->_('selected');
		}
		
		return $value;		
	}
	
//	public function isFieldUpdated($fname) {
//		$className = get_class($this);
//		$orgObj = new $className;
//		$orgObj->loadBase($this->getFieldInfo($this->_tbl_key, 'value'), false);
//		
//		return isChanged($k, $orgObj);
//	}
}