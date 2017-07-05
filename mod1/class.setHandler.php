<?php

class setHandler {
	function init() {
		$set = t3lib_div::_GP('SET');
		$this->MOD_SETTINGS['function'] = $set['function'];
		if($this->MOD_SETTINGS['function'] == "2") {  //2 = help
			$this->MOD_SETTINGS['function'] = "1";
		}
		$this->option = t3lib_div::_GP('option');
		$ph = t3lib_div::makeInstance('presetHandler');
		$ph->init();
		$this->presets = $ph->loadPresets();
	}
	
	
	function showNewSetLink() {
		global $LANG;
		$link = '
			<div>
				<a href="index.php?SET[function]='.$this->MOD_SETTINGS['function'].'&option='.$this->option.'&cmd=new">
					<input type="button" value="'.$LANG->getLL('new_set').'" />
				</a>
			</div>';
		return $link;
	}
	
	function newSet() {
		return $this->showSetForm();
	}
	
	function loadPageTypes() {
		global $TCA,$PAGES_TYPES;
		
		//read page types and icons from TCA and PAGES_TYPES
		foreach($TCA['pages']['columns']['doktype']['config']['items'] as $idx=>$params) {
			$i++;
			if($params[1] != '--div--') {
				$doktypes[$params[1]]['title'] = $params[0];
				$doktypes[$params[1]]['key'] = $params[1];
			}
		}
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_pagemanager_pagetypes','deleted = 0');
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$doktypes[$row['type_id']]['title'] = $row['title'];
			$doktypes[$row['type_id']]['key'] = $row['type_id'];
		}
		
		
		ksort($doktypes);
		return $doktypes;
	}
	
	function showSets() {
		global $LANG;
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_pagemanager_sets','deleted = 0');
		$list = '<div style="margin:10px 0 10px 0 ">';
		$list .= '<div style="margin:5px 0 10px 0">'.$LANG->getLL('manage_sets_header').'</div>';
		$counter = 0;
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
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
			$list .= '	
			
					<div style="float:left;width:200px;">'.$row['title'].'</div>';
			$list.='
					<div style="float:left;width:100px;">
					<a href="index.php?SET[function]='.$this->MOD_SETTINGS['function'].'&option='.$this->option.'&cmd=edit&set_id='.$row['uid'].'">
						<img '.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/edit2.gif','').' />
					</a>
					<a href="index.php?SET[function]='.$this->MOD_SETTINGS['function'].'&option='.$this->option.'&cmd=delete&set_id='.$row['uid'].'">
						<img '.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/garbage.gif','').' />
					</a>';
			
			
			$list.='
					<a href="index.php?SET[function]='.$this->MOD_SETTINGS['function'].'&option='.$this->option.'&cmd=view&set_id='.$row['uid'].'">
						<img '.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/zoom.gif','').' />
					</a>
					</div>
					<div style="clear:both;"></div>
				';
			$list .= '
				</div>
			';
		}
		$list.='</div>';
		return $list;
	}
	
	function createPresetDropdown($key) {
		global $LANG;
		
		if($this->info) {
			foreach($this->info['presets'] as $pr) {
				if($pr['pagetype_id'] == $key) {
					$info = $pr;
				}
			}
		}
		
		$found = false;
		foreach ($this->presets as $preset) { 
			if($preset['key'] == $key) {
				$found = true;
			}
		}
		
		$hideIsSelected = "";
		if($info['preset_id'] == 0 && !$found) {
			$hideIsSelected = 'selected="selected"';
		}
		
		$dropdown = '
			<div sytle="float:left">
			<select name="'.$key.'_preset">
				<option value="hide" '.$hideIsSelected.'>'.$LANG->getLL('hide').'</option>
		';
		
		
		foreach ($this->presets as $preset) {
			
			$selected = "";
			if($preset['key']) {
				$defaultID = $preset['key'];
				$preset['id'] = 'default'.$preset['key'];
			}
			if(strstr($info['preset_id'], "-1")) {
				
				$temp = explode(',',$info['preset_id']);
				if(intval($preset['key']) == intval($temp[1])) {
					
					$selected = 'selected="selected"';
				} 
				
			} elseif ($preset['id'] == $info['preset_id']) {
				
				$selected = 'selected="selected"';
			}  elseif ($defaultID == $key) {
				
				$selected = 'selected="selected"';
			}
			
			$dropdown .= '<option value="'.$preset['id'].'" '.$selected.'>'.$preset['title'].'</option>'."\n";
		}
		$dropdown .= '</select></div>';
		return $dropdown;
	}
	
	function save() {
		if(!t3lib_div::_GP('title')) {
			$this->errors[] = 'Enter a title!';
			$return = $this->showSetForm();
			return $return;
		}
		$pagetypes = $this->loadPageTypes();
		
		$set_title = t3lib_div::_GP('title');
		$d2t = t3lib_div::_GP('d2t');
		$customCode = t3lib_div::_GP('customCode');
		$insertFields['title'] = $set_title;
		$insertFields['d2t'] = $d2t;
		$insertFields['custom_code'] = $customCode;
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_pagemanager_sets',$insertFields);
		$set_id = $GLOBALS['TYPO3_DB']->sql_insert_id();
		foreach($pagetypes as $pagetype) {
			$insertFields = array();
			$key = $pagetype['key'];
			$preset = t3lib_div::_GP($key.'_preset');
			#print $key." --- ".$preset." <br />";
			$insertFields['set_id'] = $set_id;
			if(strstr($preset,"hide")) {
				$insertFields['preset_id'] = 0;
			} elseif (strstr($preset,"default")) {
				$preset = explode("default",$preset);
				$insertFields['preset_id'] = '-1,'.$preset[1];
			} else {
				$insertFields['preset_id'] = $preset;
			}
			$insertFields['pagetype_id'] = $pagetype['key'];
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_pagemanager_preset2set',$insertFields);
			
		}
	}
	
	function update($id) {
		
		if(!t3lib_div::_GP('title')) {
			$this->errors[] = 'Enter a title!';
			$return = $this->showSetForm($id);
			return $return;
		}
		$pagetypes = $this->loadPageTypes();
		
		$set_title = t3lib_div::_GP('title');
		$d2t = t3lib_div::_GP('d2t');
		$customCode = t3lib_div::_GP('customCode');
		$insertFields['title'] = $set_title;
		$insertFields['d2t'] = $d2t;
		$insertFields['custom_code'] = $customCode;
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_pagemanager_sets','uid='.$id,$insertFields);
		$set_id = $id;
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_pagemanager_preset2set','set_id='.$id);
		foreach($pagetypes as $pagetype) {
			$insertFields = array();
			$key = $pagetype['key'];
			$preset = t3lib_div::_GP($key.'_preset');
			#print $key." --- ".$preset." <br />";
			$insertFields['set_id'] = $set_id;
			if(strstr($preset,"hide")) {
				$insertFields['preset_id'] = 0;
			} elseif (strstr($preset,"default")) {
				$preset = explode("default",$preset);
				$insertFields['preset_id'] = '-1,'.$preset[1];
			} else {
				$insertFields['preset_id'] = $preset;
			}
			$insertFields['pagetype_id'] = $pagetype['key'];
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_pagemanager_preset2set',$insertFields);
			
		}
	}
	
	function showSetForm($id = 0) {
		global $LANG;
		$pagetypes = $this->loadPageTypes();
		if($id) {
			$this->info = $this->loadSetInfo($id);
		}
		if($this->errors) {
			foreach ($this->errors as $error) {
				$form.= '<span style="color:#dd7777">'.$error.'</span>';
			}
		}
		$form .= '
		
			<form action="index.php" method="POST" />
		';
		if($id) {
			$form .= '<input type="hidden" name="cmd" value="update" />';
			$form .= '<input type="hidden" name="id" value="'.$id.'" />';
		} else {
			$form .= '<input type="hidden" name="cmd" value="save" />';
		}

				
		$form .= '	
			<div>
				<br />
				<div>
					<div style="font-weight:bold;width:200px;float:left;">'.$LANG->getLL('title').'</div>
					<div style="float:left"><input type="text" size="50" name="title" value="'.$this->info['title'].'" /></div>
					<div style="clear:both"></div>
				</div><br />
				<div>
					<div style="font-weight:bold;width:200px;float:left;">'.$LANG->getLL('d2t').'</div>
					<div style="float:left"><input type="checkbox" value="1" name="d2t" '.($this->info['d2t']?'checked="checked"':'').'/></div>
					<div style="clear:both"></div>
				</div><br />
		';
		foreach($pagetypes as $pagetype) {
			if(substr($pagetype['title'],0,4) == "LLL:") {
				$title = $LANG->sL($pagetype['title']);
			} else {
				$title = $pagetype['title'];
			}
			$form .= '
				<div>
					<div style="font-weight:bold;width:200px;float:left;">'.$title.'</div>
					'.$this->createPresetDropdown($pagetype['key']).'
					<div style="clear:both"></div>
				</div>
			
			';
		}
		
		$form .= '
				<br />
				<div>
					<div style="font-weight:bold;width:200px;">'.$LANG->getLL('custom_code').'</div>
					<div style="float:left"><textarea name="customCode" style="width:400px;height:150px;">'.$this->info['customCode'].'</textarea></div>
					<div style="clear:both"></div>
				</div><br />
				<div>
					<a href="index.php?SET[function]='.$this->MOD_SETTINGS['function'].'&option='.$this->option.'">
						<input type="button" value="'.$LANG->getLL('back').'" />
					</a>
					<input type="submit" value="'.$LANG->getLL('save').'" />
				</div>
			</div>
		';
		return $form;
	}
	
	function isChecked() {
	}
	
	function delete($id) {
		print "delete".$id;
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_pagemanager_sets','uid='.$id);		
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_pagemanager_preset2set','set_id='.$id);
	}
	
	function loadSetInfo($id) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_pagemanager_sets','deleted = 0 AND uid='.$id);
		if($res) {
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$set['title'] = $row['title'];
			$set['d2t'] = $row['d2t'];
			$set['customCode'] = $row['custom_code'];
		}
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_pagemanager_preset2set','set_id='.$id);
		$set['presets'] = array();
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$set['presets'][] = array(
				'pagetype_id' => $row['pagetype_id'],
				'preset_id' => $row['preset_id']
			);
		}
		
		return $set;
	}
	
	function view($id) {
		global $LANG;
		$info = $this->loadSetInfo($id);
		$pagetypes = $this->loadPageTypes();
		$view = '
			<div>
				<div>
					<div style="font-weight:bold;width:200px;float:left;">'.$LANG->getLL('title').'</div>
					<div style="float:left">'.$info['title'].'</div>
					<div style="clear:both"></div>
				</div>
				<div>
					<div style="font-weight:bold;width:200px;float:left;">'.$LANG->getLL('d2t_label').'</div>
					<div style="float:left">'.($info['d2t']?$LANG->getLL('yes'):$LANG->getLL('no')).'</div>
					<div style="clear:both"></div>
				</div>
		';
		foreach ($info['presets'] as $preset) {
			$title = $pagetypes[$preset['pagetype_id']]['title'];
			if(substr($pagetypes[$preset['pagetype_id']]['title'],0,4) == 'LLL:') {
				$title = $LANG->sL($pagetypes[$preset['pagetype_id']]['title']);
			}
			if (strstr($preset['preset_id'],'-1')) {
				$temp = explode(',',$preset['preset_id']);
				$preset_title = $pagetypes[$temp[1]]['title'];
				if(substr($pagetypes[$temp[1]]['title'],0,4) == 'LLL:') {
					$preset_title = $LANG->sL($pagetypes[$temp[1]]['title']);
				}
			} elseif ($preset['preset_id'] == 0) {
				$preset_title = $LANG->getLL('hide');
			} else {
				foreach ($this->presets as $preset_record) {
					
					if($preset_record['id'] == $preset['preset_id']) {
						$preset_title = $preset_record['title'];
					} 
				}
			}
			$view .='
				
				<div>
					<div style="font-weight:bold;width:200px;float:left;">'.$title.'</div>
					<div style="float:left">'.$preset_title.'</div>
					<div style="clear:both"></div>
				</div>
			';
		}
		$view .='
			<div>
				<div style="font-weight:bold;width:200px;float:left;">'.$LANG->getLL('custom_code').'</div>
				<div style="float:left">'.$info['customCode'].'</div>
				<div style="clear:both"></div>
			</div>
			<a href="index.php?SET[function]='.$this->MOD_SETTINGS['function'].'&option='.$this->option.'">
				<input type="button" value="'.$LANG->getLL('back').'" />
			</a>
			</div>
		
		
		';
		return $view;
	}
	
	function edit($id) {
		return $this->showSetForm($id);
	}
}

?>