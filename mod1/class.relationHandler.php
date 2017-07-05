<?php

class relationHandler {
	function init() {
		$set = t3lib_div::_GP('SET');
		$this->MOD_SETTINGS['function'] = $set['function'];
		if($this->MOD_SETTINGS['function'] == "2") {  //2 = help
			$this->MOD_SETTINGS['function'] = "1";
		}
		$this->option = t3lib_div::_GP('option');
		$this->sets = array();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_pagemanager_sets','deleted = 0');
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$this->sets[] = $row;
		}
	}
	
	
	
	function showSets() {
		global $LANG;
		if(!empty($this->sets)) {
			$list = '<form action="'.$_SERVER['PHP_SELF'].'" method="POST" />';
			$list .= $this->addJS();
			$list .= '<div style="margin:5px 0 10px 0">'.$LANG->getLL('manage_relations_header').'</div>';
			$list .= '
			
				<div style="margin:10px 0 10px 0">
					
				
						<input type="hidden" name="cmd" value="save" />
				';
			
			foreach($this->sets as $row) {
				if(!isset($_POST[$row['uid'].'_rootpage'])) {
					$value = $row['rootpages'];
				} else {
					$value = t3lib_div::_GP($row['uid'].'_rootpage');
				}
				#print "GP:".t3lib_div::_GP($row['uid'].'_rootpage')."<br>";
				#print "POST:".$_POST[$row['uid'].'_rootpage']."<br>";
				#print_r($_POST);
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
						<div style="font-weight:bold;width:200px;float:left">
							<span>'.$row['title'].'</span>
						</div>
				';
				$list.='
						<div style="float:left;">
							<input type="text" name="'.$row['uid'].'_rootpage" value="'.$value.'"/>
						</div>
						<div style="clear:both"></div>
			
					</div>
					
				';
			}
			
			$list.='
					<div style="margin-top:10px">
						
						<input type="submit" value="'.$LANG->getLL('save').'"/>
					</div>
			
			
			</div></form>';
		} else {
			$list = '<div style="margin:5px 0 10px 0">'.$LANG->getLL('no_sets').'</div>';
		}
		return $list;
	}

	
	function save() {
		
		foreach ($this->sets as $set) {
			if(t3lib_div::_GP($set['uid'].'_rootpage')) {
				
				$rootpages = t3lib_div::trimExplode(',',t3lib_div::_GP($set['uid'].'_rootpage'));
				foreach($rootpages as $rootpage) {
					$subtrees[$rootpage] = $set['uid'];
				}
			}
		}
		foreach($subtrees as $rootpage=>$set_id) {
			$sets[$set_id][] = $rootpage;
		}
		foreach($sets as $set_id=>$rootpages) {
			$rootpages = implode(',',$rootpages);
			$insertFields = array(
				"rootpages" => $rootpages
			);
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_pagemanager_sets','uid='.$set_id,$insertFields);
		}
		
	}
	
	function addJS() {
		global $BACK_PATH;
		$return = "";
		return $return;

	}
}

?>