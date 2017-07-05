<?php
require_once (PATH_t3lib.'class.t3lib_tcemain.php');
class outputHandler {
	function init($subtrees) {
		$set = t3lib_div::_GP('SET');
		$this->MOD_SETTINGS['function'] = $set['function'];
		$this->subtrees = $subtrees;
		if(!$this->subtrees) {
			$this->subtrees = array();
		}
		$ph = t3lib_div::makeInstance('presetHandler');
		$ph->init();
		$this->presets = $ph->loadPresets();
		$this->doktypes = $ph->loadTCAPageInfo();
		
		ksort($this->doktypes);
		array_unique($this->doktypes);
		#print_r($this->doktypes);
		#print_r($this->presets);
		$this->fetchData();
	}
	
	function clearCache() {
			// Creating TCEmain object
		$tce = t3lib_div::makeInstance('t3lib_TCEmain');
		$tce->start('','');
		$tce->clear_cacheCmd('temp_CACHED');
	}
	
	function fetchData() {
		
		foreach($this->subtrees as $rootpage=>$set_id) {
			
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,d2t','tx_pagemanager_sets','uid='.$set_id);
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$set_info[$row['uid']]['d2t'] = $row['d2t'];
			}
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_pagemanager_preset2set','set_id='.$set_id);
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				
				
				
				if(strstr($row['preset_id'],"-1")) {
					$temp = explode(',',$row['preset_id']);
					$doktype = $temp[1];
					if(!$doktype) {
						$doktype = "0";
					}
					#print $row['pagetype_id']."<br>";
					#print_r($this->doktypes[$doktype]);
					if($this->doktypes[$row['pagetype_id']]) {
						
						$set_info[$set_id]['page_info'][$row['pagetype_id']]['label'] = $this->doktypes[$row['pagetype_id']]['label'];
						$set_info[$set_id]['page_info'][$row['pagetype_id']]['icon'] = $this->doktypes[$row['pagetype_id']]['icon'];
					} else {
						$res_pt = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_pagemanager_pagetypes','type_id='.$row['pagetype_id']);
						$row_pt = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_pt);
						$set_info[$set_id]['page_info'][$row['pagetype_id']]['label'] = $row_pt['title'];
						$set_info[$set_id]['page_info'][$row['pagetype_id']]['icon'] = $row_pt['icon'];
					}
					
						
					
					$set_info[$set_id]['page_info'][$row['pagetype_id']]['showitem'] = $this->doktypes[$doktype]['showitem'];
					if($this->doktypes[$doktype]['allowedTables']) {
						$set_info[$set_id]['page_info'][$row['pagetype_id']]['allowedTables'] = $this->doktypes[$doktype]['allowedTables'];
					}
					if(isset($this->doktypes[$doktype]['onlyAllowedTables'])) {
						$set_info[$set_id]['page_info'][$row['pagetype_id']]['onlyAllowedTables'] = $this->doktypes[$doktype]['onlyAllowedTables']?"1":"0";
					}
					if($this->doktypes[$doktype]['type']) {
						$set_info[$set_id]['page_info'][$row['pagetype_id']]['type'] = $this->doktypes[$doktype]['type'];
					}
				} elseif ($row['preset_id'] != 0) {
					#print $row['pagetype_id']."<br>";
					$res_pr = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_pagemanager_presets','uid='.$row['preset_id']);
					$row_pr = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_pr);
					$settings = unserialize($row_pr['settings']);
					$label = $this->doktypes[$row['pagetype_id']]['label'];
					$icon = $this->doktypes[$row['pagetype_id']]['icon'];
					if(!$this->doktypes[$row['pagetype_id']]) {
						$res_pt = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_pagemanager_pagetypes','type_id='.$row['pagetype_id']);
						$row_pt = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_pt);
						$label = $row_pt['title'];
						$icon = $row_pt['icon'];
					}
					$doktype = $row['pagetype_id'];
					#print_r($settings);
					$set_info[$set_id]['page_info'][$doktype]['label'] = $label;
					$set_info[$set_id]['page_info'][$doktype]['icon'] = $icon;
					$set_info[$set_id]['page_info'][$doktype]['showitem'] = $settings['showitem'];
					$set_info[$set_id]['page_info'][$doktype]['allowedTables'] = $settings['allowedTables'];
					$set_info[$set_id]['page_info'][$doktype]['onlyAllowedTables'] = $settings['onlyAllowedTables'];
					$set_info[$set_id]['page_info'][$doktype]['type'] = $settings['type'];
					
				}
			}
			
		}
		
		$this->set_info = $set_info;
	}
	
	function unsetGlobalData() {
		global $PAGES_TYPES,$TCA;
		unset($PAGES_TYPES);
		unset($TCA['pages']['columns']['doktype']['config']['items']);
		unset($TCA['pages']['ctrl']['dividers2tabs']);
		unset($TCA['pages']['types']);
		
	}
	
	function rewriteGlobalData($set_id) {
		
		global $PAGES_TYPES,$TCA;
		
		
		$data = $this->set_info[$set_id];
		#print_r($data);
		unset($PAGES_TYPES);
		$this->pages_types = array();
		#print_r($data);
		$TCA['pages']['ctrl']['dividers2tabs'] = $data['d2t'];
		if(is_array($data['page_info'])) {
			ksort($data['page_info']);
			foreach($data['page_info'] as $doktype=>$page_info) {
				$this->pages_types[$doktype]['icon'] = $page_info['icon'];
				if($page_info['allowedTables']) {
					$this->pages_types[$doktype]['allowedTables'] = $page_info['allowedTables'];
				}
				if(isset($page_info['onlyAllowedTables'])) {
					$this->pages_types[$doktype]['onlyAllowedTables'] = $page_info['onlyAllowedTables']?"1":"0";
				}
				if($page_info['type']) {
					$this->pages_types[$doktype]['type'] = $page_info['type'];
				}
				
				$TCA['pages']['columns']['doktype']['config']['items'][] = array(
					"0" => $page_info['label'],
					"1" => $doktype
				);
				if($doktype == "7") {
					$TCA['pages']['columns']['doktype']['config']['items'][] = array(
						"0" => "-----",
						"1" => "--div--"
					);
				}
				$TCA['pages']['types'][$doktype]['showitem'] = $page_info['showitem'];
			}
		}
		$PAGES_TYPES = $this->pages_types;
	}
	
	function arrays2String() {
		global $PAGES_TYPES,$TCA;
		
		$output = "";
		$output .= "\t\t".'t3lib_div::loadTCA(\'pages\');'."\n";
		
		$output .= '
		unset($TCA[\'pages\'][\'columns\'][\'doktype\'][\'config\'][\'items\']);
		unset($TCA[\'pages\'][\'ctrl\'][\'dividers2tabs\']);
		unset($TCA[\'pages\'][\'types\']);'."\n";
		$output .= "\t\t".'$TCA[\'pages\'][\'ctrl\'][\'dividers2tabs\'] = '.($TCA['pages']['ctrl']['dividers2tabs'] ? "1":"0").';'."\n";
		
		//convert $TCA['pages']['types'] to string
		#print_r($TCA['pages']['types']);
			if(is_array($TCA['pages']['types'])) {
			foreach ($TCA['pages']['types'] as $key=>$props) {
				
				$output .= "\t\t".'$TCA[\'pages\'][\'types\'][\''.$key.'\'][\'showitem\'] = "'.$props['showitem'].'";'."\n";
			}
		}
		
		//convert $TCA['pages']['columns']['doktype']['config']['items'] to string
		if(is_array($TCA['pages']['columns']['doktype']['config']['items'])) {
			foreach ($TCA['pages']['columns']['doktype']['config']['items'] as $idx=>$props) {
				$output .= "\t\t".'$TCA[\'pages\'][\'columns\'][\'doktype\'][\'config\'][\'items\']['.$idx.'][0] = "'.$props[0].'";'."\n";
				$output .= "\t\t".'$TCA[\'pages\'][\'columns\'][\'doktype\'][\'config\'][\'items\']['.$idx.'][1] = "'.$props[1].'";'."\n";
			}
		}
		
		return $output;
	}
	
	function addCustomPageTypes() {
		global $PAGES_TYPES,$TCA;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_pagemanager_pagetypes','deleted=0');
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			
			$PAGES_TYPES[$row['type_id']]['icon'] = $row['icon'];
			$exists = false;
			foreach($TCA['pages']['columns']['doktype']['config']['items'] as $idx=>$item) {
				if($item[1] == $row['type_id']) {
					$exists = true;
				}
			}
			if(!$exists) {
				$TCA['pages']['columns']['doktype']['config']['items'][] = array(1=>$row['type_id'],0=>$row['title']);
			}
		}
		return $PAGES_TYPES;
	}
	
	function getCustomPageTypes() {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_pagemanager_pagetypes','deleted=0');
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			
			$types[$row['type_id']]['icon'] = $row['icon'];
			$types[$row['type_id']]['settings'] = array(1=>$row['type_id'],0=>$row['title']);
		}
		return $types;
	}
	
	function globalStuff2String() {
		global $PAGES_TYPES,$TCA;

		
		$outputstring = "";
		/*$temp = $PAGES_TYPES['default'];
		unset($PAGES_TYPES);
		$PAGES_TYPES = array();
		foreach($this->doktypes as $key=>$properties) {
			if($properties['type']) {
				$PAGES_TYPES[$key]['type'] = $properties['type'];
			}
			if($properties['allowedTables']) {
				$PAGES_TYPES[$key]['allowedTables'] = $properties['allowedTables'];
			}
			if(isset($properties['onlyAllowedTables'])) {
				$PAGES_TYPES[$key]['onlyAllowedTables'] = $properties['onlyAllowedTables'];
			}
			#$PAGES_TYPES['showitem'] = $properties['showitem'];
			$PAGES_TYPES[$key]['icon'] = $properties['icon'];
		}
		$PAGES_TYPES['default'] = $temp;
		#print_r($PAGES_TYPES);
		
		
		$PAGES_TYPES = $new_pages_types;*/
		$new_pages_types = $this->getCustomPageTypes();
		foreach ($new_pages_types as $key=>$props) {
			foreach($props as $title=>$value) {
				if(is_array($value)) {
					$outputstring .= "\t\t".'$TCA[\'pages\'][\'columns\'][\'doktype\'][\'config\'][\'items\'][] = array(0 =>"'.$value[0].'",1 =>"'.$value[1].'");'."\n";
				} else {
					$outputstring .= "\t\t".'$PAGES_TYPES[\''.$key.'\'][\''.$title.'\'] = "'.$value.'";'."\n";
				}
			}
		}
		foreach($TCA['pages']['columns']['doktype']['config']['items'] as $idx=>$item) {
			if($new_pages_types[$item[1]]) {
				
			}
		}
		$outputstring .= '
if (TYPO3_MODE=="BE")   {
	$pid = t3lib_div::_GP("id");
	$edit = t3lib_div::_GP("edit");
	if (!$pid && is_array($edit[\'pages\'])) {
		$pid = array_shift(array_keys($edit[\'pages\']));
	}
	$rootline = t3lib_BEfunc::BEgetRootLine($pid);
	if (is_array($rootline)) {
		$RLPIDs = array();
		foreach ($rootline as $rl) {
			$RLPIDs[] = $rl[\'uid\'];
		}
	}'."\n";
		foreach($this->subtrees as $rootpage=>$set_id) {
			$sets[$set_id][] = $rootpage;
		}
		if(!$sets) {
			$sets = array();
		}
		foreach($sets as $set_id=>$rootpages) {
			$this->unsetGlobalData();
			$this->rewriteGlobalData($set_id);
			$outputstring .= "\t".'if (';
			$count = count($sets[$set_id]);
			$loop_count = 0;
			foreach ($rootpages as $rootpage) {
				if($loop_count < ($count-1)) {
					$outputstring .= 'in_array('.$rootpage.', $RLPIDs) || ';
				} else {
					$outputstring .= 'in_array('.$rootpage.', $RLPIDs)';
				}
				$loop_count++;
			}
			$outputstring .= ') {'."\n";
			$outputstring .= $this->arrays2String();
			$outputstring .= $this->getCustomCode($set_id);
			$outputstring .= "\t".'}'."\n";
			
		}
		$outputstring .= "}\n";
		return $outputstring;
	}
	
	function getCustomCode($set_id) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('custom_code','tx_pagemanager_sets','uid='.$set_id);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		return ($row['custom_code']);
	}
	
	function generateFile() {
		$fp = fopen("orig_ext_tables.conf","r");
		$orig = fread($fp,filesize("orig_ext_tables.conf"));
		fclose($fp);
		
		$outputstring = "<?php\n";
		$outputstring .= $orig."\n\n";
		$outputstring .= $this->globalStuff2String()."\n\n";
		$outputstring .= "\n?>";
		
		$ext_tables = fopen("../ext_tables.php","w+");
		fwrite($ext_tables,$outputstring);
		fclose($ext_tables);
		$this->clearCache();
		
	}
	
	function generateDefaultFile() {
		$fp = fopen("orig_ext_tables.conf","r");
		$orig = fread($fp,filesize("orig_ext_tables.conf"));
		fclose($fp);
		$outputstring = "<?php\n";
		$outputstring .= $orig;
		$outputstring .= "\n?>";
		$ext_tables = fopen("../ext_tables.php","w+");
		fwrite($ext_tables,$outputstring);
		fclose($ext_tables);
		$this->clearCache();
	}
	
	
	
}

?>