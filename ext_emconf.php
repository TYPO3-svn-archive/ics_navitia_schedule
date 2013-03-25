<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "ics_navitia_schedule".
 *
 * Auto generated 25-03-2013 15:48
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'NAViTiA Schedule display',
	'description' => 'This extension display a schedule module using NAViTiA.',
	'category' => 'plugin',
	'author' => 'In Cité Solution',
	'author_email' => 'technique@in-cite.net',
	'shy' => '',
	'dependencies' => 'ics_libnavitia',
	'conflicts' => '',
	'suggests' => 'ics_bookmarks,ics_linepicto',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.0.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.0-0.0.0',
			'ics_libnavitia' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'ics_bookmarks' => '0.0.0-0.0.0',
			'ics_linepicto' => '0.0.0-0.0.0',
		),
	),
	'_md5_values_when_last_written' => 'a:27:{s:9:"ChangeLog";s:4:"9260";s:16:"ext_autoload.php";s:4:"e038";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"dcc4";s:14:"ext_tables.php";s:4:"bf46";s:16:"locallang_db.xml";s:4:"b459";s:10:"README.txt";s:4:"ee2d";s:48:"Classes/class.tx_icsnavitiaschedule_bookmark.php";s:4:"d774";s:54:"Classes/class.tx_icsnavitiaschedule_departureBoard.php";s:4:"ac9d";s:53:"Classes/class.tx_icsnavitiaschedule_directionList.php";s:4:"9fbc";s:48:"Classes/class.tx_icsnavitiaschedule_lineList.php";s:4:"d47c";s:53:"Classes/class.tx_icsnavitiaschedule_nextDeparture.php";s:4:"2ff6";s:48:"Classes/class.tx_icsnavitiaschedule_stopList.php";s:4:"c593";s:39:"pi1/class.tx_icsnavitiaschedule_pi1.php";s:4:"1187";s:17:"pi1/locallang.xml";s:4:"9f6d";s:24:"pi1/static/constants.txt";s:4:"ba25";s:20:"pi1/static/setup.txt";s:4:"16de";s:30:"res/flexforms/flexform_ds1.xml";s:4:"2a2e";s:31:"res/flexforms/locallang_ds1.xml";s:4:"4621";s:20:"res/icons/delete.png";s:4:"32da";s:18:"res/icons/down.png";s:4:"f357";s:16:"res/icons/up.png";s:4:"95a1";s:51:"res/templates/template_schedule_departureBoard.html";s:4:"a38e";s:50:"res/templates/template_schedule_directionList.html";s:4:"1f5f";s:45:"res/templates/template_schedule_lineList.html";s:4:"2e4d";s:50:"res/templates/template_schedule_nextDeparture.html";s:4:"1de1";s:45:"res/templates/template_schedule_stopList.html";s:4:"dc25";}',
);

?>