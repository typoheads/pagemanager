<?php

class pagetypeHandler {
	function init() {
		$set = t3lib_div::_GP('SET');
		$this->MOD_SETTINGS['function'] = $set['function'];
		if($this->MOD_SETTINGS['function'] == "2") {  //2 = help
			$this->MOD_SETTINGS['function'] = "1";
		}
		$this->option = t3lib_div::_GP('option');
	}
	
	function readDefaultPageTypes() {
		global $LANG,$TCA,$PAGES_TYPES;
		$defaultPageTypes = array();
		$idx = 0;
		foreach($TCA['pages']['columns']['doktype']['config']['items'] as $props) {
			$props[1] = (int)$props[1];
		
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','tx_pagemanager_pagetypes','type_id='.$props[1].' AND deleted = 0');
			if($GLOBALS['TYPO3_DB']->sql_num_rows($res) == 0) {
				switch($props[1]) {
					case 1:case 2:case 3:case 4:case 5:case 6:case 7:case 199:case 254:case 255:
						$defaultPageTypes[$idx] = array(
							'title' => $LANG->sL($props[0]),
							'key' => $props[1],
							'default' => 1,
						);
						if($PAGES_TYPES[$props[1]]['icon']) {
								$defaultPageTypes[$idx]['icon'] = 'gfx/i/'.$PAGES_TYPES[$props[1]]['icon'];
						}
					break;
				}
				
			}
			$idx++;
		}
		
		foreach($defaultPageTypes as $index=>$pagetype) {
				$temp = $defaultPageTypes;
				unset($temp[$index]);
				foreach ($temp as $idx=>$search) {
					if($pagetype['key'] == $search['key']) {
						unset($defaultPageTypes[$idx]);
						$found = 1;
						break;
					}
				}
				if($found) {
					break;
				}
			}
		#$defaultPageTypes = array_unique($defaultPageTypes);
		return $defaultPageTypes;
	}
	
	function readCustomPageTypes() {
		$customPageTypes = array();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_pagemanager_pagetypes','deleted = 0');
		$idx = 0;
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$customPageTypes[$idx]['title'] = $row['title'];
			$customPageTypes[$idx]['key'] = $row['type_id'];
			$customPageTypes[$idx]['icon'] = $row['icon'];
			$idx++;
		}
		return $customPageTypes;
	}
	
	function loadPageTypes() {
		$defaultPageTypes = $this->readDefaultPageTypes();
		$customPageTypes = $this->readCustomPageTypes();
		if(is_array($customPageTypes)) {
			return array_merge($defaultPageTypes,$customPageTypes);
		}
		return $defaultPageTypes;
	}
	
	function getTitle($title) {
		global $LANG;
		if(substr($title,0,4) == "LLL:") {
			$title = $LANG->sL($title);	
		}
		return $title;
	}
	
	function showPageTypes() {
		global $LANG,$PAGES_TYPES;
		$pagetypes = $this->loadPageTypes();
		
		$list = '<div style="margin:10px 0 10px 0">';
		$list .= '<div style="margin:5px 0 10px 0">'.$LANG->getLL('manage_pagetypes_header').'</div>';
		$counter = 0;
		foreach($pagetypes as $pagetype) {
			if($pagetype['icon'] == '') {
				$pagetype['icon'] = 'gfx/i/'.$PAGES_TYPES['default']['icon'];
			}
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
			$title = $this->getTitle($pagetype['title']);
			$list .= '
					<div style="float:left;padding:2px 5px 2px 0;width:20px">
					<img '.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],$pagetype['icon'],'').' />
					</div>
			';
			if($pagetype['default']) {
				$list.= '
			
					<div style="width:250px;float:left;color:#777">
						<span>'.$LANG->getLL('defaultPagetype').' '.$title.'</span>
					</div>
				';
				
			} else {
				$list.= '
			
					<div style="width:250px;float:left;">
						<span>'.$title.'</span>
					</div>
				';
			}
			$list .= '<div style="float:left">';
			if(!$pagetype['default']) {
				$list.='
						<a href="index.php?SET[function]='.$this->MOD_SETTINGS['function'].'&option='.$this->option.'&cmd=edit&key='.$pagetype['key'].'">
							<img '.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/edit2.gif','').' />
						</a>
						<a href="index.php?SET[function]='.$this->MOD_SETTINGS['function'].'&option='.$this->option.'&cmd=delete&key='.$pagetype['key'].'">
							<img '.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/garbage.gif','').' />
						</a>';
			}
			
			$list.='
					<a href="index.php?SET[function]='.$this->MOD_SETTINGS['function'].'&option='.$this->option.'&cmd=view&key='.$pagetype['key'].'">
						<img '.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/zoom.gif','').' />
					</a>
				</div>
				<div style="clear:both"></div>';
			$list .= '
				</div>
			';
		}
		$list .= '
			</div>
		';
		return $list;
		
	}
	
	function showNewPageTypeLink() {
		global $LANG;
		$link = '
			<div>
				<a href="index.php?SET[function]='.$this->MOD_SETTINGS['function'].'&option='.$this->option.'&cmd=new">
					<input type="button" value="'.$LANG->getLL('new_pagetype').'" />
				</a>
			</div>';
		return $link;
	}
	
	function loadValues($key) {
		global $PAGES_TYPES,$TCA,$LANG;
		if($PAGES_TYPES[$key]) {
			$val['key'] = $key;
			foreach($TCA['pages']['columns']['doktype']['config']['items'] as $item) {
				if($item[1] == $key) {
					$val['title'] = $item[0];		
				}
			}
			$val['icon'] = $PAGES_TYPES[$key]['icon'];
		} else {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_pagemanager_pagetypes','type_id='.$key.' AND deleted = 0');
			if($res) {
				$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				
				$val['title'] = $row['title'];
				$val['key'] = $row['type_id'];
				$val['icon'] = $row['icon'];
			}
		}
		
		return $val;
	}
	
	function view($key) {
		global $LANG,$PAGES_TYPES;
		$pagetypes = $this->loadPageTypes();
		
		foreach($pagetypes as $pagetype) {
			if($pagetype['key'] == $key) {
				$page = $pagetype;
			}
		}
		if($page['icon'] == '') {
			$page['icon'] = 'gfx/i/'.$PAGES_TYPES['default']['icon'];
		}
		
		$values = $this->loadValues($key);
		$title = $this->getTitle($values['title']);
		$view = '
			<div>
				<span style="font-weight:bold">
					'.$LANG->getLL('icon').'
				</span><br />
				<div style="padding-left:10px">
					<img '.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],$page['icon'],'').' />
				</div>
				<span style="font-weight:bold">
					'.$LANG->getLL('key').'
				</span><br />
				<div style="padding-left:10px">
					'.$values['key'].'
				</div>
				<span style="font-weight:bold">
					'.$LANG->getLL('title').'
				</span><br />
				<div style="padding-left:10px">
					'.$title.'
				</div>
				<div>
					<a href="index.php?SET[function]='.$this->MOD_SETTINGS['function'].'&option='.$this->option.'">
						<input type="button" value="'.$LANG->getLL('back').'" />
					</a>
				</div>
			</div>
		';
		return $view;
	}
	
	function newPageType() {
		return $this->showEditForm();
	}
	
	function delete($key) {
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_pagemanager_pagetypes','type_id='.$key);		
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_pagemanager_preset2set',' pagetype_id='.$key);
	}
	
	function edit($key) {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','tx_pagemanager_pagetypes','type_id='.$key);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		return $this->showEditForm($row['uid'],$key);
	}
	
	function showEditForm($uid = 0,$key = 0) {
		global $LANG;
		if($this->errors) {
			foreach ($this->errors as $error) {
				$form .= '<span style="color:dd7777">'.$error."</span><br />";
			}
		}
		if(!$uid) {
			$uid = t3lib_div::_GP('uid');
		}
		if($key) {
			$values = $this->loadValues($key);
		}
		$form .= '
			<form action="index.php" method="POST">
			<input type="hidden" name="uid" value="'.$uid.'" />
		';
		if($key || $uid) {
			$form .= '
				<input type="hidden" name="cmd" value="update" />
			';
		} else {
			$form .= '
				<input type="hidden" name="cmd" value="save" />
			';
		}
		$form .= '
			<div>
				<span style="font-weight:bold">
					'.$LANG->getLL('icon').'
				</span><br />
				<span style="color:#636363">
					'.$LANG->getLL('icon_desc').'
				</span>
				<div style="padding-left:10px">
					<input type="text" size="50" name="icon" value="'.(t3lib_div::_GP('icon')? t3lib_div::_GP('icon'):$values['icon']).'"/>
				</div>
				<span style="font-weight:bold">
					'.$LANG->getLL('key').'
				</span><br />
				<span style="color:#636363">
					'.$LANG->getLL('key_desc').'
				</span>
				<div style="padding-left:10px">';
		if($key || $uid) {
			$form .= '
				<input type="text" name="key" size="3" readonly="readonly" value="'.(t3lib_div::_GP('key')? t3lib_div::_GP('key'):$values['key']).'"/>
			';
		
		} else {
			$form .= '
				<input type="text" name="key" size="3" value="'.(t3lib_div::_GP('key')? t3lib_div::_GP('key'):$values['key']).'"/>
			';
		}
		$form .= '
				</div>
				<span style="font-weight:bold">
					'.$LANG->getLL('title').'
				</span><br />
				<span style="color:#636363">
					'.$LANG->getLL('title_desc').'
				</span>
				<div style="padding-left:10px">
					<input type="text" size="50" name="title" value="'.(t3lib_div::_GP('title')? t3lib_div::_GP('title'):$values['title']).'" />
				</div>
				<div>
					<a href="index.php?SET[function]='.$this->MOD_SETTINGS['function'].'&option='.$this->option.'">
						<input type="button" value="'.$LANG->getLL('back').'" />
					</a>
					<input type="submit" value="'.$LANG->getLL('save').'" />
				</div>
			</div>
			</form>
		';
		
		return $form;
	}
	
	function keyExists() {
		global $PAGES_TYPES;
		$key = t3lib_div::_GP('key');
		$uid = t3lib_div::_GP('uid');
		$where = 'type_id='.$key;
		if($this->errors) {
			return false;
		}
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_pagemanager_pagetypes',$where);
		if($res) {
			if($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				if($uid && $row['uid'] != $uid) {
					return true;
				} elseif(!$uid) {
					return true;
				}
				
			}
		}
		switch($key) {
			case 1:case 2:case 3:case 4:case 5:case 6:case 7:case 199:case 254:case 255:
				return true;
			break;
		}
		return false;
	}
	
	function save() {
		if(t3lib_div::_GP('key') == "" || intval(t3lib_div::_GP('key')) <= 0) {
			$this->errors[] = "Enter a key > 0";
			$content = $this->showEditForm();
		} elseif(intval(t3lib_div::_GP('key')) >= 255) {
			$this->errors[] = "Enter a key <= 255";
			$content = $this->showEditForm();
		} elseif($this->keyExists()) {
			$this->errors[] = "Key exists";
			$content = $this->showEditForm();
		} elseif(!t3lib_div::_GP('title')) {
			$this->errors[] = "Enter a title";
			$content = $this->showEditForm();
		} else {
			$insertFields['title'] = t3lib_div::_GP('title');
			$insertFields['icon'] = $this->formatIconPath(t3lib_div::_GP('icon'));
			$insertFields['type_id'] = t3lib_div::_GP('key');
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_pagemanager_pagetypes',$insertFields);
		}
		
		return $content;
	}
	
	function update($key) {
		if(t3lib_div::_GP('key') == "") {
			$this->errors[] = "Enter a key";
			$content = $this->showEditForm(t3lib_div::_GP('uid'));
		} elseif(intval(t3lib_div::_GP('key')) >= 255) {
			$this->errors[] = "Enter a key <= 255";
			$content = $this->showEditForm();
		} elseif($this->keyExists()) {
			$this->errors[] = "Key exists";
			$content = $this->showEditForm();
		} elseif(!t3lib_div::_GP('title')) {
			$this->errors[] = "Enter a title";
			$content = $this->showEditForm(t3lib_div::_GP('uid'));
		} else {
			$updateFields['title'] = t3lib_div::_GP('title');
			$updateFields['icon'] = $this->formatIconPath(t3lib_div::_GP('icon'));
			$updateFields['type_id'] = t3lib_div::_GP('key');
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_pagemanager_pagetypes','uid='.t3lib_div::_GP('uid'),$updateFields);
		}
		
		return $content;	
	}
	
	function formatIconPath($path) {
		//if first character is a /, remove that thing
		if(substr($path,0,1) == "/") {
			$path = substr($path,1,strlen($path));
		}
		//if the path is relative to typo root, add ../
		if(substr($path,0,6) != 'typo3/' && substr($path,0,4) != 'gfx/') {
			$path = '../'.$path;
		}
		//if the path is relative to typo root and in typo3 folder, remove typo3/
		if (substr($path,0,6) == 'typo3/') {
			$path = substr($path,6,strlen($path));
		}
		
		return $path;
	}
	
	
}
	
	
?>