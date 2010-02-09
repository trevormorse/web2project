<?php
	require_once W2P_BASE_DIR . '/lib/adodb/adodb.inc.php';

	class BaseUpgradeManager {
		protected $configDir = '';
		protected $configFile = '';
		protected $uploadDir = '';
		protected $languageDir = '';
		protected $tempDir = '';
		protected $configOptions = array();
		protected $updatesApplied = array();
		
		public function getConfigDir() {
			return $this->configDir;
		}
		
		public function getConfigFile() {
			return $this->configFile;
		}
		
		public function getUploadDir() {
			return $this->uploadDir;
		}
		
		public function getLanguageDir() {
			return $this->languageDir;
		}
		
		public function getTempDir() {
			return $this->tempDir;
		}
		
		public function &getConfigOptions() {
			return $this->configOptions;
		}
		
		private function _setConfigOptions($dbConfig) {
			$this->configOptions = $dbConfig;
		}
		
		public function &getUpdatesApplied() {
			return $this->updatesApplied;
		}
		
		public function createConfigString($dbConfig) {
			$configFile = file_get_contents('../includes/config-dist.php');
			$configFile = str_replace('[DBTYPE]', $dbConfig['dbtype'], $configFile);
			$configFile = str_replace('[DBHOST]', $dbConfig['dbhost'], $configFile);
			$configFile = str_replace('[DBNAME]', $dbConfig['dbname'], $configFile);
			$configFile = str_replace('[DBUSER]', $dbConfig['dbuser'], $configFile);
			$configFile = str_replace('[DBPASS]', $dbConfig['dbpass'], $configFile);
			$configFile = str_replace('[DBPREFIX]', '', $configFile);
			//TODO: add support for configurable persistent connections
			$configFile = trim($configFile);

			return $configFile;
		}
		
		public function getMaxFileUpload() {
			$maxfileuploadsize = min($this->_getIniSize(ini_get('upload_max_filesize')), $this->_getIniSize(ini_get('post_max_size')));
			$memory_limit = $this->_getIniSize(ini_get('memory_limit'));
			if ($memory_limit > 0 && $memory_limit < $maxfileuploadsize) $maxfileuploadsize = $memory_limit;
			
			// Convert back to human readable numbers
			if ($maxfileuploadsize > 1048576) {
				$maxfileuploadsize = (int)($maxfileuploadsize / 1048576) . 'M';
			} 
			else if ($maxfileuploadsize > 1024) {
				$maxfileuploadsize = (int)($maxfileuploadsize / 1024) . 'K';
			}
			
			return $maxfileuploadsize;
		}
		
		public function testDatabaseCredentials($w2Pconfig) {
			$result = false;

			$this->_setConfigOptions($w2Pconfig);

			$dbConn = $this->_openDBConnection();

			if ($dbConn->_errorMsg == '') {
				$result = true;
			}

			return $result;
		}

		private function _getIniSize($val) {
			$val = trim($val);
			if (strlen($val <= 1)) return $val;
			$last = $val{strlen($val)-1};
			
			switch($last) {
				case 'k':
				case 'K':
					return (int) $val * 1024;
					break;
				case 'm':
				case 'M':
					return (int)   $val * 1048576;
					break;
				default:
					return $val;
			}
		}
		
		protected function _getMaxVersion($base_dir) {
			$migrations = array();
			$path = $base_dir.'/install/sql/'.$this->configOptions['dbtype'];
			$dir_handle = @opendir($path) or die("Unable to open $path");

			while ($file = readdir($dir_handle)) {
			   $migrationNumber = (int)substr($file, 0, 3);
			   
			   if ($migrationNumber > 0) {
			     $migrations[$migrationNumber] = $file;
			   }
			}
			
			sort($migrations);
			return $migrations;
		}
		
		protected function _prepareConfiguration() {
			$this->configDir = W2P_BASE_DIR.'/includes';
			$this->configFile = W2P_BASE_DIR.'/includes/config.php';
			$this->uploadDir = W2P_BASE_DIR.'/files';
			$this->languageDir = W2P_BASE_DIR.'/locales/en';
			$this->tempDir = W2P_BASE_DIR.'/files/temp';
		}
		
		protected function _applySQLUpdates($base_dir, $sqlfile, $dbConn) {
			$sqlfile = $base_dir.'/install/sql/'.$this->configOptions['dbtype'].'/'.$sqlfile;
			
			if (!file_exists($sqlfile) || filesize($sqlfile) == 0) {
				return array();
			}

			$query = fread(fopen($sqlfile, "r"), filesize($sqlfile));
			$pieces  = $this->_splitSQLUpdates($query);
			$pieceCount = count($pieces);
			$errorMessages = array();

			for ($i=0; $i < $pieceCount; $i++) {
				$pieces[$i] = trim($pieces[$i]);

				if(!empty($pieces[$i]) && $pieces[$i] != "#") {
					/*
					 * While this seems like a good place to use the core classes, it's
					 * really not.  With all of the dependencies, it just gets to be a huge
					 * pain and ends up loading all kinds of unnecessary stuff.
					 */
					if (strpos($pieces[$i], '[ADMINPASS]') !== false) {
						$pieces[$i] = str_replace('[ADMINPASS]', $this->configOptions['adminpass'], $pieces[$i]);
					}

					if (!$result = $dbConn->Execute($pieces[$i])) {
            $errorMessage = $dbConn->ErrorMsg();
            /*
             * TODO: I'm not happy with this solution but have yet to come up
             * 	with another way of solving it...
             */
            if (strpos($errorMessage, 'Duplicate column name') === false &&
              strpos($errorMessage, 'column/key exists') === false) {
              $dbErr = true;
              $errorMessages[] = $errorMessage;            	
            }
					}
				}
			}

			return $errorMessages;
		}
		
		private function _splitSQLUpdates($sql) {
			return explode(';', $sql);
		}
		
		protected function _openDBConnection() {
			/*
			 * While this seems like a good place to use the core classes, it's
			 * really not.  With all of the dependencies, it just gets to be a huge
			 * pain and ends up loading all kinds of unnecessary stuff.
			 */
			$db = false;

			try {
				$db = NewADOConnection($this->configOptions['dbtype']);

				if(!empty($db)) {
				  $dbConnection = $db->Connect($this->configOptions['dbhost'], $this->configOptions['dbuser'], $this->configOptions['dbpass']);

				  if ($dbConnection) {
				    $existing_db = $db->SelectDB($this->configOptions['dbname']);
				  }
				} 
				else { 
					$dbConnection = false;
				}
			} 
			catch (Exception $exc) {
				echo 'Your database credentials do not work.';
			}
			
			return $db;
		}
	}
?>