<?php
	class ModuleUpgradeManager extends BaseUpgradeManager {
		public function init() {
			global $w2Pconfig;

			$this->_prepareConfiguration();

			if (!file_exists($this->configFile) || filesize($this->configFile) == 0) {
				return false;
			} 
			else {
				require_once $this->configFile;

				//TODO: Add check to see if the user has access to system admin
				if (isset($w2Pconfig)) {
					$this->configOptions = $w2Pconfig;
				}
			}
			return true;
		}
		
		/**
		 * @param string $module the name of the module
		 * @param integer $currentVersion Represents the currentVersion of upgrade of the database. 
		 * Upon return, it contains the new current version. 
		 * @return array $allErrors representing any and all errors found during the upgrade 
		 */
		public function upgradeModule($module, &$currentVersion = 0) {
			global $m;
			$allErrors = array();
			
			//TODO: add support for database prefixes
			$dbConn = $this->_openDBConnection();

			if ($dbConn) {
				$basedir = W2P_BASE_DIR.'/modules/'.$module;
				$migrations = $this->_getMaxVersion($basedir);
				$last_index = $currentVersion;

				if ($currentVersion < count($migrations)) {
					foreach ($migrations as $update) {
						$myIndex = (int)substr($update, 0, 3);
						
						if ($myIndex > $currentVersion) {
							$last_index = $myIndex;
							$errorMessages = $this->_applySQLUpdates($basedir, $update, $dbConn);
							$allErrors = array_merge($allErrors, $errorMessages);
							$this->updatesApplied[] = $update;
						}
					}
				}
				
				$currentVersion = $last_index;				
			} 
			else {
				$allErrors[] = 'Update failed. Database connection was not found.';
			}

			return $allErrors;
		}
	}		
?>