<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$TYPO3_CONF_VARS['EXTCONF']['ics_bookmarks'][$_EXTKEY] = 'tx_icsnavitiaschedule_bookmark';

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_icsnavitiaschedule_pi1.php', '_pi1', 'list_type', 0);
?>