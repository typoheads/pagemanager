<?php
t3lib_div::loadTCA('pages');
/*$pageItems = &$TCA['pages']['columns']['doktype']['config']['items'];

$PAGES_TYPES['66'] = array(
	  'type' => '',
	  'allowedTables' => '',
	  'onlyAllowedTables' => '0'
);
t3lib_SpriteManager::addTcaTypeIcon('pages', '66', '../fileadmin/templates/img/pagetypes/artikel.png');
array_push($pageItems, array('Artikelseite', '66'));
*/

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

//		$PAGES_TYPES['66']['icon'] = "../fileadmin/templates/img/pagetypes/artikel.gif";
//		$TCA['pages']['columns']['doktype']['config']['items'][] = array(0 =>"Artikelseite",1 =>"66", '../fileadmin/templates/img/pagetypes/artikel.gif');
//		$PAGES_TYPES['99']['icon'] = "../fileadmin/templates/img/pagetypes/uebersicht.gif";
//		$TCA['pages']['columns']['doktype']['config']['items'][] = array(0 =>"Kapitelseite",1 =>"99");
//		$PAGES_TYPES['33']['icon'] = "../fileadmin/templates/img/pagetypes/plugin.gif";
//		$TCA['pages']['columns']['doktype']['config']['items'][] = array(0 =>"Pluginseite",1 =>"33");
		$PAGES_TYPES['3']['icon'] = "../fileadmin/templates/img/pagetypes/pages_link.gif";
		$PAGES_TYPES['4']['icon'] = "../fileadmin/templates/img/pagetypes/pages_shortcut.gif";
		$PAGES_TYPES['254']['icon'] = "../fileadmin/templates/img/pagetypes/pages_sysf.gif";
//		$PAGES_TYPES['44']['icon'] = "../fileadmin/templates/img/pagetypes/wiki.gif";
//		$TCA['pages']['columns']['doktype']['config']['items'][] = array(0 =>"Wiki",1 =>"44");

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

$TCA['pages']['columns']['th_boxenspalte'] = Array (
    'label' => 'LLL:fileadmin/templates/ext/locallang_db.php:th_boxenspalte',
    'exclude' => 1,
    'config' => Array (
        'type' => 'select',
	'size' => '1',
	'maxItems' => '1',
        'items' => Array (
        	Array('LLL:fileadmin/templates/ext/locallang_db.php:th_boxenspalte.I.0', 0),
		Array('LLL:fileadmin/templates/ext/locallang_db.php:th_boxenspalte.I.1', 1),
        ),
        'default' => '0',
        'iconsInOptionTags' => 0,
    )
);


	if (in_array(63, $RLPIDs)) {

$TCA['pages']['columns']['subtitle']['label'] = 'LLL:fileadmin/templates/ext/locallang_db.php:subtitle';
$TCA['pages']['columns']['abstract']['label'] = 'LLL:fileadmin/templates/ext/locallang_db.php:abstract';


$TCA['pages']['columns']['th_news_overview_headline'] = array(
'exclude' => 0,
'label' => "LLL:fileadmin/templates/ext/locallang_db.php:th_news_overview_headline",
'config' => array(
'type' => 'input',
'wizards' => array(
'tx_loremipsum' => array(
'type'=>'userFunc',
'userFunc'=>'EXT:lorem_ipsum/class.tx_loremipsum_wiz.php:tx_loremipsum_wiz->main',
'params'=>array(
'type' => 'title',
)))));

$TCA['pages']['columns']['th_news_overview_headline']['config']['wizards']['tx_loremipsum']['userFunc'] = 'EXT:lorem_ipsum/class.tx_loremipsum_wiz.php:tx_loremipsum_wiz->main';

$TCA['pages']['columns']['lastUpdated'] = array(
'exclude' => 0,
'label' => "LLL:fileadmin/templates/ext/locallang_db.php:lastUpdated",
'config' => array(
'type' => 'input',
'size' => 12,
'max' => 20,
'eval' => 'datetime',
'checkbox' => 0,
'default' => 0,
));

# print "hier";

$TCA['pages']['columns']['th_organisationseinheit'] = array(
'exclude' => 0,
'label' => "LLL:fileadmin/templates/ext/locallang_db.php:th_organisationseinheit",
'config' => array(
'type' => 'select',
'size' => 1,
'maxitems' => 1,
'foreign_table' => 'tx_organisationseinheit',
'items' => Array(
Array('LLL:fileadmin/templates/ext/locallang_db.php:th_organisationseinheit.leer', 0),
),
));

$TCA['pages']['columns']['th_news_overview_image'] = array(
'exclude' => 0,
'label' => 'LLL:fileadmin/templates/ext/locallang_db.php:th_news_overview_image',
'config' => Array (
'type' => 'group',
'internal_type' => 'file',
'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],
'uploadfolder' => 'uploads/pics',
'show_thumbs' => '1',
'size' => '1',
'maxitems' => '1',
'minitems' => '0',
'autoSizeMax' => 40,
)
);



#		print_r($TCA['pages']['types']);

	}
#print_r($RLPIDs);
	if (in_array(59, $RLPIDs) || in_array(739, $RLPIDs) || in_array(738, $RLPIDs)) {

//		t3lib_div::loadTCA('pages');

//		unset($TCA['pages']['columns']['doktype']['config']['items']);
#		unset($TCA['pages']['ctrl']['dividers2tabs']);
#		unset($TCA['pages']['types']);
#		print_r($TCA['pages']['types']);
//		$TCA['pages']['ctrl']['dividers2tabs'] = 1;
//		# 44 = Wikiseite
//		$TCA['pages']['types']['44']['showitem'] = "--div--;Titel,doktype,hidden,title,subtitle,nav_hide,--div--; Erweitert,TSconfig;;;;1-1-1, --div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.access, --palette--;LLL:EXT:cms/locallang_tca.xml:pages.palettes.visibility;visibility, --palette--;LLL:EXT:cms/locallang_tca.xml:pages.palettes.access;access";
//		$TCA['pages']['columns']['doktype']['config']['items'][0][0] = "Wikiseite";
//		$TCA['pages']['columns']['doktype']['config']['items'][0][1] = "44";
	}

    # fe-user changes:
    $TCA['fe_users']['columns']['usergroup']['config']['size'] = 20;
    $TCA['be_users']['columns']['usergroup']['config']['size'] = 20;
    $TCA['tt_content']['columns']['media']['config']['size'] = 20;
}

$TCA['tt_content']['columns']['altText']['config']['eval'] = 'required';



?>