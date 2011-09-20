<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 In Cit� Solution <technique@in-cite.net>
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
 
class tx_icsnavitiaschedule_stopList {

	public function __construct($pObj) {
		$this->pObj = $pObj;
	}

	function getStopsList($dataProvider, $lineExternalCode, $forward = true) {
		$templatePart = $this->pObj->templates['stopList'];
		$template = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_SCHEDULE_STOP_LIST###');
		$stops = $dataProvider->getRoutePointList($lineExternalCode, $forward);
		//$stopTot = $stops->Count();
		
		$markers = array(
			'###PREFIXID###' => $this->pObj->prefixId,
			'###STOP_LIST_TITLE###' => $this->pObj->pi_getLL('stopList.title')
		);
		
		if($this->pObj->debug) {
			$this->debugParam = t3lib_div::_GP($this->pObj->debug_param);
		}
		
		$stopListTemplate = $this->pObj->cObj->getSubpart($template, '###STOP_LIST###');
		foreach($stops->ToArray() as $stop)  {
			$markers['###STOP_NAME###'] = $stop->stopPoint->name;
			$markers['###URL###'] = $this->pObj->pi_linkTP_keepPIvars_url(array('stopPointExternalCode' => $stop->stopPoint->externalCode));
			
			if(!is_null($this->debugParam)) {
				$markers['###URL###'] .= '&' . $this->pObj->debug_param . '=' . $this->debugParam;
			}
	
			$stopListContent .= $this->pObj->cObj->substituteMarkerArray($stopListTemplate, $markers);
		}
		
		$template = $this->pObj->cObj->substituteSubpart($template, '###STOP_LIST###', $stopListContent);
		$content .= $this->pObj->cObj->substituteMarkerArray($template, $markers);
		return $content;
	}
	
}