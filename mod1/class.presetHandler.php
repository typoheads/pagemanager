<?php

class presetHandler {
	function init() {
		global $PAGES_TYPES;
		$set = t3lib_div::_GP('SET');
		$this->MOD_SETTINGS['function'] = $set['function'];
		if($this->MOD_SETTINGS['function'] == "2") {  //2 = help
			$this->MOD_SETTINGS['function'] = "1";
		}
		#print_r($PAGES_TYPES);
	}
	
	function deletePreset($id) {
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_pagemanager_presets','uid='.$id);
	}
	
	function updatePreset($id) {
		$dataArray['type'] = t3lib_div::_GP('type');
		$dataArray['allowedTables'] = $this->prepareAllowedTables();
		$dataArray['onlyAllowedTables'] = t3lib_div::_GP('onlyAllowedTables');
		$dataArray['showitem'] = t3lib_div::_GP('showitem');
		if(!strstr($dataArray['showitem'],'doktype')) {
			if(substr($dataArray['showitem'],strlen($dataArray['showitem']),-1) != ',') {
				$dataArray['showitem'] .= ',';
			}
			$dataArray['showitem'] .= 'doktype';
		}
		$title = t3lib_div::_GP('title');
		$updateFields['title'] = $title;
		$updateFields['settings'] = serialize($dataArray);
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_pagemanager_presets','uid='.$id,$updateFields);
	}
	
	function getAllowedTablesField() {
		global $LANG;
		$allowedTables = t3lib_div::trimExplode(',',$this->prefillValues['allowedTables']);
		
		$availableTables = $GLOBALS['TYPO3_DB']->admin_get_tables();
		if($allowedTables[0] == '*') {
			$allowedTables = $availableTables;
		} elseif($allowedTables == '') {
			$allowedTables = array();
		}
		$field = '<div id="allowedTables" style="border:1px solid #000;height:200px;overflow:auto">';
		foreach($availableTables as $table) {
			$selected = "";
			if(in_array($table,$allowedTables)) {
				$selected = 'checked="checked"';
			}
		
			$field .= '<input type="checkbox" name="allowedTables[]" value="'.$table.'" '.$selected.' />'.$table.'<br/>';
		}
		$field .= '</div>
			<a href="#" onclick="checkAll(\'allowedTables\')">'.$LANG->getLL('select_all').'</a> / 
			<a href="#" onclick="uncheckAll(\'allowedTables\')">'.$LANG->getLL('deselect_all').'</a>
		
		
		';
		return $field;
	}
	
	function getShowItemField() {
		$return = '<textarea name="showitem" rows="10" cols="50">'.$this->prefillValues['showitem'].'</textarea>';
		return $return;
	}
	
	function loadPrefillValues($id) {
		$customPresets = array();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_pagemanager_presets','uid='.$id.' AND deleted = 0');
		if($res) {
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$customPresets['title'] = $row['title'];
			$customPresets['id'] = $row['uid'];
			$settings = unserialize($row['settings']);
			$customPresets['type'] = $settings['type'];
			$customPresets['showitem'] = $settings['showitem'];
			$customPresets['onlyAllowedTables'] = $settings['onlyAllowedTables'] ? "1":"0";
			$customPresets['allowedTables'] = $settings['allowedTables'];
		}
		
		return $customPresets;
	}
	
	function loadTCAPageInfo($key = 0) {
		global $TCA,$PAGES_TYPES;
		//read page types and icons from TCA and PAGES_TYPES
		foreach($TCA['pages']['columns']['doktype']['config']['items'] as $idx=>$params) {
			#switch($params[1]) {
			#	case 1:case 2:case 3: case 4: case 5: case 6: case 7: case 199: case 254:case 255:
			if($params[1] != '--div--') {
					$i++;
					
					$doktypes[$params[1]]['label'] = $params[0];
					if($PAGES_TYPES['default']['icon']) {
						$iconfile = $PAGES_TYPES[$params[1]]['icon'];
						if(!$PAGES_TYPES[$params[1]]['icon']) {
							$iconfile = $PAGES_TYPES['default']['icon'];
						}
						#print_r($PAGES_TYPES);
						$doktypes[$params[1]]['icon'] = $iconfile;
					}
					if($PAGES_TYPES[$params[1]]['allowedTables']) {
						$allowedTables = $PAGES_TYPES[$params[1]]['allowedTables'];
						if(!isset($PAGES_TYPES[$params[1]]['allowedTables'])) {
							$allowedTables = $PAGES_TYPES['default']['allowedTables'];
						}
						$doktypes[$params[1]]['allowedTables'] = $allowedTables;
					}
					if($PAGES_TYPES[$params[1]]['type']) {
						$type = $PAGES_TYPES[$params[1]]['type'];
						if(!$type) {
							$type = $PAGES_TYPES['default']['type'];
						}
						#print $type;
						$doktypes[$params[1]]['type'] = $type;
					}
					if($PAGES_TYPES[$params[1]]['onlyAllowedTables']) {
						$onlyAllowedTables = $PAGES_TYPES[$params[1]]['onlyAllowedTables'] ? "1":"0";
						if(!isset($PAGES_TYPES[$params[1]]['onlyAllowedTables'])) {
							$onlyAllowedTables = $PAGES_TYPES['default']['onlyAllowedTables'] ? "1":"0";
						}
						
						$doktypes[$params[1]]['onlyAllowedTables'] = $onlyAllowedTables;
					}
					$showItem = $TCA['pages']['types'][$params[1]]['showitem'];
					$doktypes[$params[1]]['showitem'] = $showItem;
					
			
			}
			
			
		}
		ksort($doktypes);
		if($key) {
			return $doktypes[$key];
		}
		return $doktypes;
	}
	
	function showPresetForm($id = 0,$cmd="save") {
		global $LANG;
		$form = $this->addJS();
		if($id) {
			$this->prefillValues = $this->loadPrefillValues($id);
		}
		$form .= '
			<div style="margin:10px 0 0 10px">
			<form action="index.php" method="POST">
			<input type="hidden" name="cmd" value="'.$cmd.'" />
		';
		if($cmd == "update") {
			$form .= '
				<input type="hidden" name="id" value="'.$id.'" />
			';
		}
		
		
		$form .= '
			<input type="hidden" name="SET[function]" value"'.$this->MOD_SETTINGS['function'].'" />
				<div >
				 <label style="margin-bottom:15px;display:block;width:100px;float:left;">'.$LANG->getLL('title').'</label>
				 <div style="float:left;"><input type="text" size="50" name="title" value="'.$this->prefillValues['title'].'" /></div>
				 <div style="clear:both"></div>
				</div><br />
				<div>
					<label style="margin-bottom:15px;display:block;width:100px;float:left;">'.$LANG->getLL('type').'</label>
					<div style="float:left;">
						<select name="type">
							<option value="sys" '.$this->isSelected('type','sys').'>'.$LANG->getLL('sys').'</option>
							<option value="web" '.$this->isSelected('type','web').'>'.$LANG->getLL('web').'</option>
						</select>
					</div>
					<div style="clear:both"></div>
				</div>
				<div>
					<label style="margin-bottom:15px;display:block;width:100px;float:left;">'.$LANG->getLL('allowedTables').'</label>
					<div style="float:left;">
						'.$this->getAllowedTablesField().'
					</div>
					<div style="clear:both"></div>
				</div><br />
				<div>
					<label style="margin-bottom:15px;display:block;width:100px;float:left;">'.$LANG->getLL('onlyAllowedTables').'</label>
					<div style="float:left;">
						<input type="radio" name="onlyAllowedTables" value="1" '.$this->isChecked('onlyAllowedTables','1').'/>'.$LANG->getLL('yes').'<br />
						<input type="radio" name="onlyAllowedTables" value="0" '.$this->isChecked('onlyAllowedTables','0').'/>'.$LANG->getLL('no').'<br />
					</div>
					<div style="clear:both"></div>
				</div>
				<div>
					<label style="margin-bottom:15px;display:block;width:100px;float:left;">'.$LANG->getLL('showitem').'</label>
					<div style="float:left;">
						'.$this->getShowItemField().'
					</div>
					<div style="clear:both"></div>
				</div>
				
				
				<a href="index.php?SET[function]='.$this->MOD_SETTINGS['function'].'">
					<input type="button" value="'.$LANG->getLL('back').'" />
				</a>
				<input type="submit" value="'.$LANG->getLL('save').'" />
			</form>
			</div>
		
		';
		return $form;
	}
	
	function isSelected($field,$value) {
		if($this->prefillValues[$field] == $value) {
			return 'selected="selected"';
		}
	}
	
	function isChecked($field,$value) {
		
		if($this->prefillValues[$field] == $value) {
			return 'checked="checked"';
		}
	}
	
	function addJS() {
		$return = '
		<script type="text/javascript">
			function checkAll(id) {
				var inputs = document.getElementById(id).getElementsByTagName(\'input\');
			
				for (i=0;i<inputs.length;i++) {
					inputs[i].checked = \'checked\';
				}
			}
			
			function uncheckAll(id) {
				var inputs = document.getElementById(id).getElementsByTagName(\'input\');
			
				for (i=0;i<inputs.length;i++) {
					inputs[i].checked = \'\';
				}
			}
		</script>';
		return $return;
	}
	
	function newPreset() {
		return $this->showPresetForm();
	}
	
	function editPreset($id) {
		return $this->showPresetForm($id,"update");
	}
	
	function prepareAllowedTables() {
		$allowedTables = t3lib_div::_GP('allowedTables');
		#print_r($allowedTables);
		if(count($allowedTables) == 0) {
			return "";
		}
		$availableTables = $GLOBALS['TYPO3_DB']->admin_get_tables();
		if(count($allowedTables) == count($availableTables)) {
			return "*";
		}
		return implode(',',$allowedTables);
	}
	
	function savePreset() {
		$dataArray['type'] = t3lib_div::_GP('type');
		$dataArray['allowedTables'] = $this->prepareAllowedTables();
		$dataArray['onlyAllowedTables'] = t3lib_div::_GP('onlyAllowedTables');
		$dataArray['showitem'] = t3lib_div::_GP('showitem');
		if(!strstr($dataArray['showitem'],'doktype')) {
			if(substr($dataArray['showitem'],strlen($dataArray['showitem']),-1) != ',') {
				$dataArray['showitem'] .= ',';
			}
			$dataArray['showitem'] .= 'doktype';
		}
		$title = t3lib_div::_GP('title');
		$insertFields['title'] = $title;
		$insertFields['settings'] = serialize($dataArray);
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_pagemanager_presets',$insertFields);
	}
	
	function showNewPresetLink() {
		global $LANG;
		$link = '
			<div>
				<a href="index.php?SET[function]='.$this->MOD_SETTINGS['function'].'&cmd=new">
					<input type="button" value="'.$LANG->getLL('new_preset').'" />
				</a>
			</div>';
		return $link;
	}
	
	function viewPreset($id = 0,$key = 0) {
		global $LANG;
		if($id) {
			$values = $this->loadPrefillValues($id);
		} elseif($key) {
			$values = $this->loadTCAPageInfo($key);
		}
		
		if($values['title']) {
			$title = $values['title'];
		} else {
			$title = $LANG->sL($values['label']);
		}
		
		#print_r($values);
		$view = '
			<div style="margin:10px 0 10px 0">
				<div style="background-color:#ccc;padding:2px 0 0 5px;">
					<span style="font-weight:bold">
						'.$LANG->getLL('title').'
					</span><br />
					<div style="padding-left:10px">
						'.$title.'
					</div>
				</div>
				<div style="background-color:#ddd;padding:2px 0 0 5px;">
					<span style="font-weight:bold">
						'.$LANG->getLL('type').'
					</span><br />
					<div style="padding-left:10px">
						'.$values['type'].'
					</div>
				</div>
				<div style="background-color:#ccc;padding:2px 0 0 5px;">
					<span style="font-weight:bold">
						'.$LANG->getLL('allowedTables').'
					</span><br />
					<div style="padding-left:10px">
		';
		if($values['allowedTables'] == "*") {
			$view .= $LANG->getLL('all');
		} elseif($values['allowedTables'] == "") {
			$view .= $LANG->getLL('none');
		} elseif(is_array($values['allowedTables'])) {
			
			foreach ($tables as $table) {
				$view .= $table."<br />";
			}
		} else {
			$tables = t3lib_div::trimExplode(',',$values['allowedTables']);
			foreach ($tables as $table) {
				$view .= $table."<br />";
			}
		}
		$view .= '
					</div>
				</div>
				<div style="background-color:#ddd;padding:2px 0 0 5px;">
					<span style="font-weight:bold">
						'.$LANG->getLL('onlyAllowedTables').'
					</span><br />
					<div style="padding-left:10px">
						'.($values['onlyAllowedTables'] ? $LANG->getLL('yes'):$LANG->getLL('no')).'
					</div>
				</div>
				<div style="background-color:#ccc;padding:2px 0 0 5px;">
					<span style="font-weight:bold">
						'.$LANG->getLL('showitem').'
					</span><br />
					<div style="padding-left:10px">
						'.$values['showitem'].'
					</div>
				</div>
				
			</div>
			<div>
				<a href="index.php?SET[function]='.$this->MOD_SETTINGS['function'].'">
					<input type="button" value="'.$LANG->getLL('back').'" />
				</a>
			</div>
		';
		return $view;
	}
	
	function loadPresets() {
		$defaultPresets = $this->readDefaultPresets();
		$customPresets = $this->readCustomPresets();
		
		return array_merge($defaultPresets,$customPresets);
	}
	
	function showPresets() {
		global $LANG;
		$presets = $this->loadPresets();
		$list = '<div style="margin:10px 0 10px 0">';
		$list .= '<div style="margin:5px 0 10px 0">'.$LANG->getLL('manage_presets_header').'</div>';
		$counter = 0;
		foreach($presets as $preset) {
			if($counter % 2 == 0) {
				$list .= '
					<div style="background-color:#ccc;padding:2px 0 0 5px;">
				';
			} else {
				$list .= '
					<div style="background-color:#ddd;padding:2px 0 0 5px;">
				';
			}
			$counter++;
			if($preset['id']) {
				$list .= '	
					<div style="float:left;width:250px;height:20px;">
						<span>'.$preset['title'].'</span>
					</div>
					<div style="float:left;height:20px;">
						<a href="index.php?SET[function]='.$this->MOD_SETTINGS['function'].'&cmd=edit&preset_id='.$preset['id'].'">
							<img '.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/edit2.gif','').' />
						</a>
						<a href="index.php?SET[function]='.$this->MOD_SETTINGS['function'].'&cmd=delete&preset_id='.$preset['id'].'">
							<img '.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/garbage.gif','').' />
						</a>
						<a href="index.php?SET[function]='.$this->MOD_SETTINGS['function'].'&cmd=view&preset_id='.$preset['id'].'">
							<img '.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/zoom.gif','').' />
						</a>
					</div>
					<div style="clear:both"></div>
				';
			} else {
				$list .= '	
					<div style="float:left;width:250px;height:20px;">
						<span style="color:#777">'.$LANG->getLL('defaultPreset').' '.$preset['title'].'</span>
					</div>
					<div style="float:left">
						<a href="index.php?SET[function]='.$this->MOD_SETTINGS['function'].'&cmd=view&key='.$preset['key'].'">
							<img '.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/zoom.gif','').' />
						</a>
					</div>
					<div style="clear:both"></div>
				';
			}
			$list .= '
				</div>
			';
		}
		$list .= '
			</div>
		';
		return $list;
	}
	
	function readCustomPresets() {
		$customPresets = array();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_pagemanager_presets','deleted = 0');
		$idx = 0;
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$customPresets[$idx]['title'] = $row['title'];
			$customPresets[$idx]['id'] = $row['uid'];
			$customPresets[$idx]['settings'] = $row['settings'];
			$idx++;
		}
		return $customPresets;
	}
	
	function readDefaultPresets() {
		global $LANG,$TCA;
		$defaultPresets = array();
		foreach($TCA['pages']['columns']['doktype']['config']['items'] as $props) {
			switch($props[1]) {
				case 1:case 2:case 3:case 4:case 5:case 6:case 7:case 199:case 254:case 255:
					$defaultPresets[] = array(
						'title' => $LANG->sL($props[0]),
						'key' => $props[1]
					);
				break;
			}
			
			
		}
		
		foreach($defaultPresets as $index=>$preset) {
			$tempPresets = $defaultPresets;
			unset($tempPresets[$index]);
			foreach ($tempPresets as $idx=>$search) {
				if($preset['key'] == $search['key']) {
					unset($defaultPresets[$idx]);
					$found = 1;
					break;
				}
			}
			if($found) {
				break;
			}
		}
		
		return $defaultPresets;
	}
}

?>