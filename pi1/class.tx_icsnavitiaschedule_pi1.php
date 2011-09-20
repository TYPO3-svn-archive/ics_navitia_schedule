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
class tx_icsnavitiaschedule_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_icsnavitiaschedule_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_icsnavitiaschedule_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'ics_navitia_schedule';	// The extension key.
	
	private $login;
	private $url;
	private $dataProvider;
	var $pictoLine;
	var $templates;
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
	
		$this->init();

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

		return $this->pi_wrapInBaseClass($content);
	}
	
	function init() {
		$this->login = $this->conf['login'];
		$this->url = $this->conf['url'];
		$this->networks = $this->conf['networks'];
		
		$this->dataProvider = t3lib_div::makeInstance('tx_icslibnavitia_APIService', $this->url, $this->login);
		$this->pictoLine = t3lib_div::makeInstance('tx_icslinepicto_getlines');
		$templateflex_file = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'template_file', 'configuration');
		$this->templates = array(
			'lineList' => $this->cObj->fileResource($templateflex_file?'uploads/tx_icsnavitiaschedule/' . $templateflex_file:$this->conf['view.']['lineList.']['templateFile']),
			'directionList' => $this->cObj->fileResource($templateflex_file?'uploads/tx_icsnavitiaschedule/' . $templateflex_file:$this->conf['view.']['directionList.']['templateFile']),
			'stopList' => $this->cObj->fileResource($templateflex_file?'uploads/tx_icsnavitiaschedule/' . $templateflex_file:$this->conf['view.']['stopList.']['templateFile']),
			'departureBoard' => $this->cObj->fileResource($templateflex_file?'uploads/tx_icsnavitiaschedule/' . $templateflex_file:$this->conf['view.']['departureBoard.']['templateFile'])
		);
		
		$libnavitia_conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ics_libnavitia']);
		$this->debug_param = $libnavitia_conf['debug_param'];
	}
	
	function getNetworkList() {
		$networkList = null;
		if (!empty($this->networks)) {
			$networks = explode(',', $this->networks);
			$networkList = $this->dataProvider->getNetworksByCodes($aNetworks);
		}
		return $networkList;
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_navitia_schedule/pi1/class.tx_icsnavitiaschedule_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ics_navitia_schedule/pi1/class.tx_icsnavitiaschedule_pi1.php']);
}

?>