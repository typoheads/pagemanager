<?php
t3lib_extMgm::addLLrefForTCAdescr('pages','fileadmin/templates/ext/locallang_csh_waff.php');

if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$TCA["tx_pagemanager_presets"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:pagemanager/locallang_db.xml:tx_pagemanager_presets',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_pagemanager_presets.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "title, settings",
	)
);

$TCA["tx_pagemanager_sets"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:pagemanager/locallang_db.xml:tx_pagemanager_sets',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_pagemanager_sets.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "",
	)
);

$TCA["tx_pagemanager_preset2set"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:pagemanager/locallang_db.xml:tx_pagemanager_preset2set',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_pagemanager_preset2set.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "preset_id, set_id, pagetype_id",
	)
);

$TCA["tx_pagemanager_pagetypes"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:pagemanager/locallang_db.xml:tx_pagemanager_pagetypes',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_pagemanager_pagetypes.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, title, type_id",
	)
);


if (TYPO3_MODE == 'BE')	{
		
	t3lib_extMgm::addModule('tools','txpagemanagerM1','',t3lib_extMgm::extPath($_EXTKEY).'mod1/');
}

		$PAGES_TYPES['34']['icon'] = "../fileadmin/tempimg/spacer_icon.gif";
		$TCA['pages']['columns']['doktype']['config']['items'][] = array(0 =>"TestType",1 =>"34");

if (TYPO3_MODE=="BE")   {
	$pid = t3lib_div::_GP("id");
	$edit = t3lib_div::_GP("edit");
	if (!$pid && is_array($edit['pages'])) {
		$pid = array_shift(array_keys($edit['pages']));
	}
	$rootline = t3lib_BEfunc::BEgetRootLine($pid);
	if (is_array($rootline)) {
		$RLPIDs = array();
		foreach ($rootline as $rl) {
			$RLPIDs[] = $rl['uid'];
		}
	}
	if (in_array(50, $RLPIDs)) {
		t3lib_div::loadTCA('pages');

		unset($TCA['pages']['columns']['doktype']['config']['items']);
		unset($TCA['pages']['ctrl']['dividers2tabs']);
		unset($TCA['pages']['types']);
		$TCA['pages']['ctrl']['dividers2tabs'] = 0;
		$TCA['pages']['types']['3']['showitem'] = "hidden;;;;1-1-1, doktype, title;;3;;2-2-2, subtitle, nav_hide, url;;;;3-3-3, urltype, TSconfig;;6;nowrap;5-5-5, storage_pid;;7, l18n_cfg";
		$TCA['pages']['types']['4']['showitem'] = "hidden;;;;1-1-1, doktype, title;;3;;2-2-2, subtitle, nav_hide, shortcut;;;;3-3-3, shortcut_mode, TSconfig;;6;nowrap;5-5-5, storage_pid;;7, l18n_cfg";
		$TCA['pages']['types']['34']['showitem'] = "hidden;;;;1-1-1, doktype;;2;button, title;;3;;2-2-2, subtitle, nav_hide, TSconfig;;6;nowrap;5-5-5, storage_pid;;7, l18n_cfg";
		$TCA['pages']['types']['254']['showitem'] = "hidden;;;;1-1-1, doktype, title;LLL:EXT:lang/locallang_general.php:LGL.title;;;2-2-2, --div--, TSconfig;;6;nowrap;5-5-5, storage_pid;;7, module";
		$TCA['pages']['types']['255']['showitem'] = "hidden;;;;1-1-1, doktype, title;;;;2-2-2";
		$TCA['pages']['columns']['doktype']['config']['items'][0][0] = "LLL:EXT:lang/locallang_general.php:LGL.external";
		$TCA['pages']['columns']['doktype']['config']['items'][0][1] = "3";
		$TCA['pages']['columns']['doktype']['config']['items'][1][0] = "LLL:EXT:cms/locallang_tca.php:pages.doktype.I.2";
		$TCA['pages']['columns']['doktype']['config']['items'][1][1] = "4";
		$TCA['pages']['columns']['doktype']['config']['items'][2][0] = "TestType";
		$TCA['pages']['columns']['doktype']['config']['items'][2][1] = "34";
		$TCA['pages']['columns']['doktype']['config']['items'][3][0] = "LLL:EXT:lang/locallang_tca.php:doktype.I.1";
		$TCA['pages']['columns']['doktype']['config']['items'][3][1] = "254";
		$TCA['pages']['columns']['doktype']['config']['items'][4][0] = "LLL:EXT:lang/locallang_tca.php:doktype.I.2";
		$TCA['pages']['columns']['doktype']['config']['items'][4][1] = "255";
	}
}



?>