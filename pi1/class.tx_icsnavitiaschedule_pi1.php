<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 In Cité Solution <technique@in-cite.net>
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

require_once(PATH_tslib.'class.tslib_pibase.php');

/**
 * Plugin 'Schedule' for the 'ics_navitia_schedule' extension.
 *
 * @author	In Cité Solution <technique@in-cite.net>
 * @package	TYPO3
 * @subpackage	tx_icsnavitiaschedule
 */
class tx_icsnavitiaschedule_pi1 extends tslib_pibase implements tx_icsbookmarks_IProvider {
	var $prefixId      = 'tx_icsnavitiaschedule_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_icsnavitiaschedule_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'ics_navitia_schedule';	// The extension key.
	
	private $login;
	private $url;
	private $dataProvider;
	public $pictoLine;
	public $templates;
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	public function main($content, $conf) {
		$this->conf = $conf;
		$this->init();

		switch ($this->mode) {
			case 'next':
				if (isset($this->piVars['lineExternalCode']) && !empty($this->piVars['lineExternalCode']) && isset($this->piVars['sens']) && isset($this->piVars['stopAreaExternalCode']) && !empty($this->piVars['stopAreaExternalCode'])) {
					$next = t3lib_div::makeInstance('tx_icsnavitiaschedule_nextDeparture', $this);
					$content = $next->renderNextDeparture($this->dataProvider, $this->piVars['lineExternalCode'], $this->piVars['stopAreaExternalCode'], $this->piVars['sens']);
				}
				break;
			case 'proximity':
				$next = t3lib_div::makeInstance('tx_icsnavitiaschedule_nextDeparture', $this);
				$content = $next->renderProximity($this->dataProvider);
				break;
			case 'bookmark':
				$next = t3lib_div::makeInstance('tx_icsnavitiaschedule_nextDeparture', $this);
				$content = $next->renderBookmarks($this->dataProvider, $this->bookmarksLimit);
				return $content;
				break;
			case 'bookmark_manage':
				$next = t3lib_div::makeInstance('tx_icsnavitiaschedule_nextDeparture', $this);
				
				if (t3lib_extMgm::isLoaded('ics_bookmarks')) {
					$libsBookmarks = t3lib_div::makeInstance('tx_icsbookmarks_libs');
					$bookmarks = $libsBookmarks->getBookmarks($this->extKey);
					$content = $this->viewBookmarks($bookmarks, true);
					//viewBookmarks
					//$bookmarksManage = t3lib_div::makeInstance('tx_icsbookmarks_manage', $this);
					//$content = $bookmarksManage->
				}
				//$content = $next->renderBookmarksManage($this->dataProvider);
				return $content;
				break;
			default:
				if (isset($this->piVars['lineExternalCode']) && !empty($this->piVars['lineExternalCode']) && isset($this->piVars['sens']) && isset($this->piVars['stopPointExternalCode']) && !empty($this->piVars['stopPointExternalCode'])) {
					$departureBoard = t3lib_div::makeInstance('tx_icsnavitiaschedule_departureBoard', $this);
					$content = $departureBoard->renderDepartureBoard($this->dataProvider, $this->piVars['lineExternalCode'], $this->piVars['sens'], $this->piVars['stopPointExternalCode']);
				}
				elseif (isset($this->piVars['lineExternalCode']) && !empty($this->piVars['lineExternalCode']) && isset($this->piVars['sens'])) {
					$stopList = t3lib_div::makeInstance('tx_icsnavitiaschedule_stopList', $this);
					$content = $stopList->getStopsList($this->dataProvider, $this->piVars['lineExternalCode'], $this->piVars['sens']);
				}
				elseif (isset($this->piVars['lineExternalCode']) && !empty($this->piVars['lineExternalCode'])) {
					$directionList = t3lib_div::makeInstance('tx_icsnavitiaschedule_directionList', $this);
					$content = $directionList->getDirectionList($this->dataProvider, $this->piVars['lineExternalCode']);
				}
				else {
					$lineList = t3lib_div::makeInstance('tx_icsnavitiaschedule_lineList', $this);
					$networkList = $this->getNetworkList();
					$content = $lineList->getLineList($this->dataProvider, $networkList);
				}
		}

		return $this->pi_wrapInBaseClass($content);
	}
	
	/**
	 * Declares onload event for geoloc init.
	 * @author Pierrick Caillon <pierrick@in-cite.net>
	 */
	public function positionLoad($content, $conf) {
		$GLOBALS['TSFE']->JSeventFuncCalls['onload'][$this->prefixId] = 'if (' . $this->prefixId . '_init) ' . $this->prefixId . '_init();';
		$conf['userFunc'] .= 'Int';
		return $this->cObj->USER($conf, 'INT');
	}
	
	/**
	 * Sets onload handler for geoloc init if required.
	 * @author Pierrick Caillon <pierrick@in-cite.net>
	 */
	public function positionLoadInt($content, $conf) {
		if (!isset($conf['refreshDelay']))
			$conf['refreshDelay'] = 120;
		$geoloc = new tx_icslibgeoloc_GeoLocation();
		if (isset($conf['errorCallback']));
			$geoloc->error = $conf['errorCallback'];
		if ((
			 ($geoloc->Position === false) || 
			 ((!$geoloc->IsManual) && ($conf['refreshDelay']) && ($geoloc->Update + $conf['refreshDelay'] < time())) || 
			 (($geoloc->IsManual) && ($conf['refreshDelayManual']) && ($geoloc->Update + $conf['refreshDelayManual'] < time()))) && 
			(!$geoloc->IsDenied)) {
			$geoloc->successUrl = t3lib_div::getIndpEnv('TYPO3_REQUEST_URL');
			$geoloc->maxAge = intval($conf['refreshDelay']);
			$geoloc->requireGps = true;
			$GLOBALS['TSFE']->additionalJavaScript[$this->prefixId] .= $this->prefixId . '_init = function() {' . $geoloc->JsCall . '();};';
		}
		return '';
	}
	
	private function init() {
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
		
		$this->pi_initPIflexForm();
		$this->login = $this->conf['login'];
		$this->url = $this->conf['url'];
		$this->networks = $this->conf['networks'];
		
		$this->dataProvider = t3lib_div::makeInstance('tx_icslibnavitia_APIService', $this->url, $this->login);
		$this->pictoLine = t3lib_div::makeInstance('tx_icslinepicto_getlines');
		$this->templates = array(
			'lineList' => $this->getTemplateFile('line', $this->conf['view.']['lineList.']['templateFile']),
			'directionList' => $this->getTemplateFile('direction', $this->conf['view.']['directionList.']['templateFile']),
			'stopList' => $this->getTemplateFile('stop', $this->conf['view.']['stopList.']['templateFile']),
			'departureBoard' => $this->getTemplateFile('departure', $this->conf['view.']['departureBoard.']['templateFile']),
			'nextDeparture' => $this->getTemplateFile('next', $this->conf['view.']['nextDeparture.']['templateFile']),
			'bookmarks' => $this->getTemplateFile('next', $this->conf['view.']['bookmarks.']['templateFile']),
		);
		
		$this->mode = $this->conf['mode'];
		$flexMode = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'display_mode');
		if ($flexMode)
			$this->mode = $flexMode;
		if ($this->piVars['mode'])
			$this->mode = $this->piVars['mode'];
			
		$this->bookmarksLimit = $this->conf['bookmarksLimit'];
		$flexBookmarksLimit = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'limit');
		if ($flexBookmarksLimit)
			$this->bookmarksLimit = $flexBookmarksLimit;
		if ($this->piVars['bookmarksLimit'])
			$this->bookmarksLimit = $this->piVars['bookmarksLimit'];
			
	}
	
	public function getNetworkList() {
		$networkList = null;
		if (!empty($this->networks)) {
			$networks = explode(',', $this->networks);
			$networkList = $this->dataProvider->getNetworksByCodes($networks);
		}
		return $networkList;
	}
	
	private function getTemplateFile($templateName, $default) {
		$flex = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], $templateName, 'templates');
		return $this->cObj->fileResource($flex ? 'uploads/tx_icsnavitiaschedule/' . $flex : $default);
	}
	
	public function getHiddenFields() {
		$params = t3lib_div::_GET();
		$arguments = array();
		foreach ($params as $name => $value) {
			if (is_array($value))
				continue;
			if ((strpos($name, 'tx_') === 0) || (strpos($name, 'user_') === 0))
				continue;
			$arguments[$name] = strval($value);
		}
		$hidden = '';
		foreach ($arguments as $name => $value)
			$hidden .= '<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />';
		return $hidden;
	}
	
	function viewBookmarks($bookmarks, $edit = false) {
		$this->conf = $GLOBALS['TSFE']->tmpl->setup['plugin.'][$this->prefixId . '.'];
		$this->init();

		$template = $this->cObj->getSubpart($this->templates['bookmarks'], '###TEMPLATE_BOOKMARKS###');
		$templateList = $this->cObj->getSubpart($template, '###TEMPLATE_LIST###');
		$libsBookmarks = t3lib_div::makeInstance('tx_icsbookmarks_libs');
		$redirect = t3lib_div::getIndpEnv('TYPO3_REQUEST_URL');
		
		$markers = array(
			'PREFIXID'		=> $this->prefixId,
			'BOOKMARKS_TITLE'	=> $this->pi_getLL('bookmarks'),
			'UP_LABEL'	=> $this->pi_getLL('bookmarks_up'),
			'DELETE_LABEL'	=> $this->pi_getLL('bookmarks_delete'),
			'DOWN_LABEL'	=> $this->pi_getLL('bookmarks_down'),
		);
		
		$listContent = '';
		$maxSorting = 0;
		if (!empty($bookmarks))
			$maxSorting = max($bookmarks);

		foreach ($bookmarks as $data => $sorting) {
			$templateListItem = $templateList;
			
			if ($edit) {
				$markers['DELETE_LINK'] = $libsBookmarks->getURL(tx_icsbookmarks_libs::ACTION_DELETE, $this->extKey, $sorting, $redirect);
				$markers['UP_LINK'] = $libsBookmarks->getURL(tx_icsbookmarks_libs::ACTION_UP, $this->extKey, $sorting, $redirect);
				$markers['DOWN_LINK'] = $libsBookmarks->getURL(tx_icsbookmarks_libs::ACTION_DOWN, $this->extKey, $sorting, $redirect);
				
				if (!$sorting)
					$templateListItem = $this->cObj->substituteSubpart($templateListItem, '###UP###', '');
				if ($sorting >= $maxSorting)
					$templateListItem = $this->cObj->substituteSubpart($templateListItem, '###DOWN###', '');
			}
			
			$aBookmarksData = unserialize($data);
			$stopArea = $this->dataProvider->getStopAreaByCode($aBookmarksData['stopAreaExternalCode']);
			$line = $this->dataProvider->getLineByCode($aBookmarksData['lineExternalCode']);
			
			$markers['STOPAREA_NAME'] = $stopArea->name;
			$markers['LINE_NAME'] = $line->name;
			
			if($aBookmarksData['forward']) {
				$markers['FORWARD'] = $line->forward->name;
			}
			else {
				$markers['FORWARD'] = $line->backward->name;
			}
			
			$timeLimit = max(1, intval($this->pObj->conf['nextDeparture.']['nextLimit']));
			$dateChangeTime = intval($this->pObj->conf['nextDeparture.']['dateChangeTime']);
			$noNextDay = intval($this->pObj->conf['nextDeparture.']['noNextDay']);
			$confBase = $this->pObj->conf['nextDeparture.']['proximity.'];
			$listContent .= $this->cObj->substituteMarkerArray($templateListItem, $markers, '###|###');
		}
		
		
		$template = $this->cObj->substituteSubpart($template, '###TEMPLATE_LIST###', $listContent);
		$content = $this->cObj->substituteMarkerArray($template, $markers, '###|###');
		return $content;
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_navitia_schedule/pi1/class.tx_icsnavitiaschedule_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_navitia_schedule/pi1/class.tx_icsnavitiaschedule_pi1.php']);
}

?>