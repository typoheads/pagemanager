<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_pagemanager_presets"] = array (
	"ctrl" => $TCA["tx_pagemanager_presets"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "title,settings"
	),
	"feInterface" => $TCA["tx_pagemanager_presets"]["feInterface"],
	"columns" => array (
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pagemanager/locallang_db.xml:tx_pagemanager_presets.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"settings" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pagemanager/locallang_db.xml:tx_pagemanager_presets.settings",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "title;;;;2-2-2, settings;;;;3-3-3")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_pagemanager_sets"] = array (
	"ctrl" => $TCA["tx_pagemanager_sets"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => ""
	),
	"feInterface" => $TCA["tx_pagemanager_sets"]["feInterface"],
	"columns" => array (
		
	),
	"types" => array (
		"0" => array("showitem" => "")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_pagemanager_preset2set"] = array (
	"ctrl" => $TCA["tx_pagemanager_preset2set"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "preset_id,set_id,pagetype_id"
	),
	"feInterface" => $TCA["tx_pagemanager_preset2set"]["feInterface"],
	"columns" => array (
		"preset_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pagemanager/locallang_db.xml:tx_pagemanager_preset2set.preset_id",		
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"set_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pagemanager/locallang_db.xml:tx_pagemanager_preset2set.set_id",		
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"pagetype_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pagemanager/locallang_db.xml:tx_pagemanager_preset2set.pagetype_id",		
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "preset_id;;;;1-1-1, set_id, pagetype_id")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_pagemanager_pagetypes"] = array (
	"ctrl" => $TCA["tx_pagemanager_pagetypes"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,title,type_id"
	),
	"feInterface" => $TCA["tx_pagemanager_pagetypes"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pagemanager/locallang_db.xml:tx_pagemanager_pagetypes.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"type_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:pagemanager/locallang_db.xml:tx_pagemanager_pagetypes.type_id",		
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, type_id;;;;3-3-3")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);
?>