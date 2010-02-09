<?php
	class UpgradeManager extends BaseUpgradeManager {
		private $action = '';

		public function getActionRequired() {
			global $w2Pconfig;

			if ($this->action == '') {
				$this->_prepareConfiguration();
				if (!file_exists($this->configFile) || filesize($this->configFile) == 0) {
					$this->action = 'install';
				} else {
					require_once $this->configFile;
					if (isset($dPconfig)) {
						$this->configOptions = $dPconfig;
						$this->action = 'conversion';
					} elseif (isset($w2Pconfig)) {
						$this->configOptions = $w2Pconfig;
						$this->action = 'upgrade';
					} else {
						/*
						 *  This  case should never be reached because if there is a config.
						 * php file, it should load either the $dPconfig or $w2Pconfig
						 * depending on whether it's an conversion or upgrade respectively.
						 * If we reach here, we have this strange situation of a mostly
						 * "configured" system that doesn't have the configuration values
						 * required.
						 */
						$this->action = 'install';
					}
				}
			}
			return $this->action;
		}
		
		public function upgradeSystem() {
			$allErrors = array();
			//TODO: add support for database prefixes

			$dbConn = $this->_openDBConnection();

			if ($dbConn) {
				$currentVersion = $this->_getDatabaseVersion($dbConn);
				$migrations = $this->_getMigrations();

				if ($currentVersion < count($migrations)) {
					foreach ($migrations as $update) {
						$myIndex = (int) substr($update, 0, 3);
						if ($myIndex > $currentVersion) {
							$this->updatesApplied[] = $update;
							$errorMessages = $this->_applySQLUpdates(W2P_BASE_DIR, $update, $dbConn);
							$allErrors = array_merge($allErrors, $errorMessages);
							$sql = "INSERT INTO w2pversion (db_version, last_db_update) VALUES ($myIndex, now())";
							$dbConn->Execute($sql);
						}
					}
				}
			} else {
				$allErrors[] = 'Update failed. Database connection was not found.';
			}

			return $allErrors;
		}
		public function getUpdatesApplied() {
			return $this->updatesApplied;
		}
		public function convertDotProject() {
			$dpVersion = '';

			$allErrors = array();
			$dbConn = $this->_openDBConnection();
			$sql = "SELECT * FROM dpversion ORDER BY db_version DESC";
			$res = $dbConn->Execute($sql);
			if ($res && $res->RecordCount() > 0) {
				$dpVersion = $res->fields['code_version'];
			}

			switch ($dpVersion) {
				case '2.0':
					$errorMessages = $this->_applySQLUpdates('dp20_to_201.sql', $dbConn);
					$allErrors = array_merge($allErrors, $errorMessages);
				case '2.0.1':
					$errorMessages = $this->_applySQLUpdates('dp201_to_202.sql', $dbConn);
					$allErrors = array_merge($allErrors, $errorMessages);
				case '2.0.2':
				case '2.0.3':
				case '2.0.4':
				case '2.1-rc1':
					$errorMessages = $this->_applySQLUpdates('dp21rc1_to_21rc2.sql', $dbConn);
					$allErrors = array_merge($allErrors, $errorMessages);
				case '2.1-rc2':
				case '2.1':
				case '2.1.1':
				case '2.1.2':
        case '2.1.3':
					$errorMessages = $this->_applySQLUpdates('dp_to_w2p1.sql', $dbConn);
					$allErrors = array_merge($allErrors, $errorMessages);

					$recordsUpdated = $this->_scrubDotProjectData($dbConn);

					$errorMessages = $this->_applySQLUpdates('dp_to_w2p2.sql', $dbConn);
					$allErrors = array_merge($allErrors, $errorMessages);

					$errorMessages = $this->upgradeSystem($dbConn);
					$allErrors = array_merge($allErrors, $errorMessages);

					break;
				default:
					$allErrors[] = "Unfortunately, we can't determine which version of dotProject you're using.  To be safe, we're not going to do anything.";
					$allErrors[] = "If you are using dotProject 1.x, please use their methods to upgrade to dotProject v2.x before you go any further.";
			}

			return $allErrors;
		}
		
		private function _scrubDotProjectData($dbConn) {			
			/*
			 * While this seems like a good place to use the core classes, it's
			 * really not.  With all of the dependencies, it just gets to be a huge
			 * pain and ends up loading all kinds of unnecessary stuff.
			 */
			$recordsUpdated = 0;

			$sql = "SELECT * FROM sysvals WHERE sysval_value like '%|%' ORDER BY sysval_id ASC";
			$res = $dbConn->Execute($sql);
			if ($res->RecordCount() > 0) {
				while (!$res->EOF) {
					$fields = $res->fields;

					$sysvalId = $fields['sysval_id'];
					$sysvalKeyId = $fields['sysval_key_id'];
					$sysvalTitle = $fields['sysval_title'];
					$values = explode("\n", $fields['sysval_value']);
					foreach ($values as $syskey) {
						$sysvalValId = substr($syskey, 0, strpos($syskey, '|'));
						$sysvalValue = substr(trim(' '.$syskey.' '), strpos($syskey, '|') + 1);
						$sql = "INSERT INTO sysvals (sysval_key_id, sysval_title, sysval_value, sysval_value_id) " .
								"VALUES ($sysvalKeyId, '$sysvalTitle', '$sysvalValue', $sysvalValId)";
						$dbConn->Execute($sql);
					}
					$recordsUpdated++;
					$sql = "DELETE FROM sysvals WHERE sysval_id = $sysvalId";
					$dbConn->Execute($sql);
					$res->MoveNext();
				}
			}
			return $recordsUpdated;
		}
	}