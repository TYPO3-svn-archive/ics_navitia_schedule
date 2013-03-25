<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Plan.Net France <typo3@plan-net.fr>
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
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */
 
class tx_icsnavitiaschedule_bookmark implements tx_icsbookmarks_IProvider {
	function viewBookmarks($bookmarks, $edit = false) {
		$conf = $GLOBALS['TSFE']->tmpl->setup['plugin.'][$this->prefixId . '.'];
		$activateBookmarks = $this->cObj->cObjGetSingle($conf['nextDeparture.']['bookmarks.']['activate'], $conf['nextDeparture.']['bookmarks.']['activate.']);
		if(!$activateBookmarks) {
			return false;
		}
		
		if(!$edit) {
			$conf['mode'] = 'bookmark';
		}
		else {
			$conf['mode'] = 'bookmark_manage';
		}
		$plugin = t3lib_div::makeInstace('tx_icsnavitiaschedule_pi1');
		$plugin->cObj = $this->cObj;
		return $plugin->main('', $conf);
	}
}
