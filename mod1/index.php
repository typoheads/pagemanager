<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Dev-Team Typoheads <dev@typoheads.at>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');

$LANG->includeLLFile('EXT:pagemanager/mod1/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
require_once('class.presetHandler.php');
require_once('class.pagetypeHandler.php');
require_once('class.setHandler.php');
require_once('class.relationHandler.php');
require_once("class.outputHandler.php");
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]



/**
 * Module 'Manage PageTCA' for the 'pagemanager' extension.
 *
 * @author	Dev-Team Typoheads <dev@typoheads.at>
 * @package	TYPO3
 * @subpackage	tx_pagemanager
 */
class  tx_pagemanager_module1 extends t3lib_SCbase {
				var $pageinfo;

				/**
				 * Initializes the Module
				 * @return	void
				 */
				function init()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					parent::init();

					/*
					if (t3lib_div::_GP('clear_all_cache'))	{
						$this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
					}
					*/
				}

				/**
				 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
				 *
				 * @return	void
				 */
				function menuConfig()	{
					global $LANG;
					$this->MOD_MENU = Array (
						'function' => Array (
							'1' => $LANG->getLL('function1'),
							'2' => $LANG->getLL('help'),
						)
					);
					parent::menuConfig();
				}

				/**
				 * Main function of the module. Write the content to $this->content
				 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
				 *
				 * @return	[type]		...
				 */
				function main()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
					
					// Access check!
					// The page will show only if there is a valid page and if this page may be viewed by the user
					$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
					$access = is_array($this->pageinfo) ? 1 : 0;

					if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

							// Draw the header.
						$this->doc = t3lib_div::makeInstance('mediumDoc');
						$this->doc->backPath = $BACK_PATH;
						$this->doc->form='<form action="" method="POST">';

							// JavaScript
						$this->doc->JScode = '
							<script language="javascript" type="text/javascript">
								script_ended = 0;
								function jumpToUrl(URL)	{
									document.location = URL;
								}
							</script>
						';
						$this->doc->postCode='
							<script language="javascript" type="text/javascript">
								script_ended = 1;
								if (top.fsMod) top.fsMod.recentIds["web"] = 0;
							</script>
						';

						$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

						$this->content.=$this->doc->startPage($LANG->getLL('mod_title'));
						$this->content.=$this->doc->header($LANG->getLL('mod_title'));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
						$this->content.=$this->doc->divider(5);


						// Render content:
						$this->moduleContent();


						// ShortCut
						if ($BE_USER->mayMakeShortcut())	{
							$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
						}

						$this->content.=$this->doc->spacer(10);
					} else {
							// If no access or if ID == zero

						$this->doc = t3lib_div::makeInstance('mediumDoc');
						$this->doc->backPath = $BACK_PATH;

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->spacer(10);
					}
				}
				
				function truncateAllTables() {
					
					$GLOBALS['TYPO3_DB']->sql_query("TRUNCATE TABLE tx_pagemanager_presets");
					$GLOBALS['TYPO3_DB']->sql_query('TRUNCATE TABLE tx_pagemanager_pagetypes');
					$GLOBALS['TYPO3_DB']->sql_query('TRUNCATE TABLE tx_pagemanager_sets');
					$GLOBALS['TYPO3_DB']->sql_query('TRUNCATE TABLE tx_pagemanager_preset2set');
				}

				/**
				 * Prints out the module HTML
				 *
				 * @return	void
				 */
				function printContent()	{

					$this->content.=$this->doc->endPage();
					echo $this->content;
				}

				/**
				 * Generates the module content
				 *
				 * @return	void
				 */
				function moduleContent()	{
					global $LANG;
					$this->cmd = t3lib_div::_GP('cmd');
					switch((string)$this->MOD_SETTINGS['function'])	{
						
						//pages
						case 1:
							$option = t3lib_div::_GP('option');
							if(!$option) {
								$option = 1;
							}
							
							$content = '
								<div style="height:30px;background-color:#c1d5ba;padding:10px 10px 0 10px">
								<div style="width:25%;float:left;">
							';
							if($this->setExists()) {
								$content .= '
									
									<a href="index.php?SET[function]='.$this->MOD_SETTINGS['function'].'&option='.$option.'&cmd=writeChanges">
									<input type="button" value="'.$LANG->getLL('writeChanges').'" />
									</a>
								';		
							} else {
								$content .= '
									
									&nbsp;
								';	
							}
							$content .= '
								</div>
								<div style="width:25%;float:left;">
									<a href="index.php?SET[function]='.$this->MOD_SETTINGS['function'].'&option='.$option.'&cmd=reset">
									<input type="button" value="'.$LANG->getLL('resetFile').'" />
									</a>
								</div>
								<div style="width:50%;text-align:right;float:left;">
										<select name="option" onchange="jumpToUrl(\'index.php?SET[function]='.$this->MOD_SETTINGS['function'].'&option=\'+this.options[this.selectedIndex].value,this);">
											<option value="1" '.$this->isSelectedOption(1).'>'.$LANG->getLL('pages_option1').'</option>
											<option value="2" '.$this->isSelectedOption(2).'>'.$LANG->getLL('pages_option2').'</option>
											<option value="3" '.$this->isSelectedOption(3).'>'.$LANG->getLL('pages_option3').'</option>
											<option value="4" '.$this->isSelectedOption(4).'>'.$LANG->getLL('pages_option4').'</option>
										</select>
								</div>
								<div style="clear:both"></div>
								</div>
							
							';
							
							
							
							if($this->cmd == "writeChanges") {
								$sets = array();
								$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_pagemanager_sets','deleted = 0');
								while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
									$sets[] = $row;
									
									
								}
								
								foreach ($sets as $set) {
									if($set['rootpages']) {
										
										$rootpages = t3lib_div::trimExplode(',',$set['rootpages']);
										foreach($rootpages as $rootpage) {
											$subtrees[$rootpage] = $set['uid'];
										}
									}
								}
								$outputHandler = t3lib_div::makeInstance('outputHandler');
								
								$outputHandler->init($subtrees);
								$outputHandler->generateFile();
								header("Location:index.php?SET[function]=".$this->MOD_SETTINGS['function'].'&option='.$option);
							}
							if($this->cmd == "reset") {
								$outputHandler = t3lib_div::makeInstance('outputHandler');
								$outputHandler->generateDefaultFile();
								$this->truncateAllTables();
								$outputHandler->clearCache();
								$content .= '<p style="margin:10px 0 10px 0;">'.$LANG->getLL('deleted').'</p>';
								$content .= '<a href="index.php?SET[function]='.$this->MOD_SETTINGS['function'].'&option='.$option.'"><input type="submit" value="'.$LANG->getLL('back').'" /></a>';
								$this->content.=$this->doc->section($LANG->getLL('deleted'),$content,0,1);
								return;
								#header("Location:index.php?SET[function]=".$this->MOD_SETTINGS['function'].'&option='.$option);
							}
							switch($option)	{
								
								//manage presets
								case 1:
									#print $this->cmd;
									$handler = t3lib_div::makeInstance('presetHandler');
									$handler->init();
									if($this->cmd) {
										switch ($this->cmd) {
											case "new":
												$content .= $handler->newPreset();	
											break;
											case "edit":
												$content .= $handler->editPreset(t3lib_div::_GP('preset_id'));	
											break;
											case "update":
												$handler->updatePreset(t3lib_div::_GP('id'));
												header("Location:index.php?SET[function]=".$this->MOD_SETTINGS['function']);
											break;
											case "delete":
												$handler->deletePreset(t3lib_div::_GP('preset_id'));
												header("Location:index.php?SET[function]=".$this->MOD_SETTINGS['function']);
											break;
											case "save":
												$handler->savePreset();
												header("Location:index.php?SET[function]=".$this->MOD_SETTINGS['function']);
											break;
											case "view":
												$id = t3lib_div::_GP('preset_id');
												$key = t3lib_div::_GP('key');
												if($id) {
													$content .= $handler->viewPreset($id);
												} else {
													$content .= $handler->viewPreset(0,$key);
												}
												
											break;
										}
									} else {
										$content .= $handler->showPresets();
										$content .= $handler->showNewPresetLink();	
									}
							
									$this->content.=$this->doc->section($LANG->getLL('manage_presets'),$content,0,1);
								break;
								
								//manage sets
								case 2:
									$handler = t3lib_div::makeInstance('setHandler');
									$handler->init();
									if($this->cmd) {
										switch($this->cmd) {
											case "new":
												$content .= $handler->newSet();
											break;
											case "save":
												$errors = $handler->save();
												if($errors == "") {
													header("Location:index.php?SET[function]=".$this->MOD_SETTINGS['function'].'&option='.$option);
												} else {
													$content .= $errors;
												}
											break;
											case "delete":
												$handler->delete(t3lib_div::_GP('set_id'));
												header("Location:index.php?SET[function]=".$this->MOD_SETTINGS['function'].'&option='.$option);
											break;
											case "view":
												$content .= $handler->view(t3lib_div::_GP('set_id'));
											break;
											case "edit":
												$content .= $handler->edit(t3lib_div::_GP('set_id'));	
											break;
											case "update":
												$errors = $handler->update(t3lib_div::_GP('set_id'));
												if($errors == "") {
													header("Location:index.php?SET[function]=".$this->MOD_SETTINGS['function'].'&option='.$option);
												} else {
													$content .= $errors;
												}
											break;
											
										}
									} else {
										$content .= $handler->showSets();
										$content .= $handler->showNewSetLink();
									}
									
									
									$this->content.=$this->doc->section($LANG->getLL('manage_sets'),$content,0,1);
								break;
								
								//manage relations
								case 3:
									$handler = t3lib_div::makeInstance('relationHandler');
									$handler->init();
									if($this->cmd) {
										switch($this->cmd) {
											
											case "save":
												$errors = $handler->save();
												if($errors == "") {
													header("Location:index.php?SET[function]=".$this->MOD_SETTINGS['function'].'&option='.$option);
												} else {
													$content .= $errors;
												}
											break;
				
											
										}
									} else {
										$content .= $handler->showSets();
										
									}
									$this->content.=$this->doc->section($LANG->getLL('manage_relations'),$content,0,1);
								break;
								
								//manage page types
								case 4:
									$handler = t3lib_div::makeInstance('pagetypeHandler');
									$handler->init();
									if($this->cmd) {
										switch ($this->cmd) {
											case "new":
												$content .= $handler->newPageType();	
											break;
											case "edit":
												
												$content .= $handler->edit(t3lib_div::_GP('key'));	
											break;
											case "update":
												$errors = $handler->update(t3lib_div::_GP('orig_key'));
												if($errors == "") {
													header("Location:index.php?SET[function]=".$this->MOD_SETTINGS['function'].'&option='.$option);
												} else {
													$content .= $errors;
												}
											break;
											case "delete":
												$handler->delete(t3lib_div::_GP('key'));
												header("Location:index.php?SET[function]=".$this->MOD_SETTINGS['function'].'&option='.$option);
											break;
											case "save":
												$errors = $handler->save();
												if($errors == "") {
													header("Location:index.php?SET[function]=".$this->MOD_SETTINGS['function'].'&option='.$option);
												} else {
													$content .= $errors;
												}
												
											break;
											case "view":
												
												$key = t3lib_div::_GP('key');
												$content .= $handler->view($key);
												
												
											break;
										}
									} else {
										$content .= $handler->showPageTypes();
										$content .= $handler->showNewPageTypeLink();	
									}
									
									
									
									$this->content.=$this->doc->section($LANG->getLL('manage_pagetypes'),$content,0,1);
								break;
							}
							
						break;
						
						//help
						case 2:
							$togglerStyles = '
								background:#A3ACB5 url(gfx/bg.gif) repeat-x scroll left bottom;
								border-color:#A6A8B5;
								border-style:solid;
								border-width:0px 1px 1px;
								color:#FFFFFF;
								padding:2px 0 2px 5px;
							';
							$content = '
								<div style="height:30px;background-color:#c1d5ba;padding:10px 10px 0 10px;">
									<h2 style="background:0;text-align:center;">'.$LANG->getLL('howto').'</h2>
								</div>
								<div id="help">
									<h3 class="toggler" style="'.$togglerStyles.'">'.$LANG->getLL('help_section1_header').'</h3>
									<div class="element">'.nl2br($LANG->getLL('help_section1')).'</div>
									<h3 class="toggler" style="'.$togglerStyles.'">'.$LANG->getLL('help_section2_header').'</h3>
									<div class="element">'.nl2br($LANG->getLL('help_section2')).'</div>
									<h3 class="toggler" style="'.$togglerStyles.'">'.$LANG->getLL('help_section3_header').'</h3>
									<div class="element">'.nl2br($LANG->getLL('help_section3')).'</div>
									<h3 class="toggler" style="'.$togglerStyles.'">'.$LANG->getLL('help_section4_header').'</h3>
									<div class="element">'.nl2br($LANG->getLL('help_section4')).'</div>
									<h3 class="toggler" style="'.$togglerStyles.'">'.$LANG->getLL('help_section5_header').'</h3>
									<div class="element">'.nl2br($LANG->getLL('help_section5')).'</div>
								</div>
							';
							$this->content.=$this->doc->section($LANG->getLL('help'),$content,0,1);
						break;
					}
					
					
				}
				
				function isSelectedOption($value) {
					if(t3lib_div::_GP('option') == $value) {
						return 'selected="selected"';
					}
				}
				
				function setExists() {
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid','tx_pagemanager_sets','deleted = 0 AND rootpages <> ""');
					return $GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0;
				}
				
			}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pagemanager/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/pagemanager/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_pagemanager_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>