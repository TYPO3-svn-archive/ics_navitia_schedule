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
 
class tx_icsnavitiaschedule_stopList {

	public function __construct($pObj) {
		$this->pObj = $pObj;
	}

	public function getStopsList($dataProvider, $lineExternalCode, $forward = true) {
		$templatePart = $this->pObj->templates['stopList'];
		$template = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_SCHEDULE_STOP_LIST###');
		$stops = $dataProvider->getRoutePointList($lineExternalCode, $forward);
		
		$markers = array(
			'###PREFIXID###' => $this->pObj->prefixId,
			'###STOP_LIST_TITLE###' => $this->pObj->pi_getLL('stopList.title'),
		);
		
		$stopListTemplate = $this->pObj->cObj->getSubpart($template, '###STOP_LIST###');
		
		$stopNum = 0;
		foreach ($stops->ToArray() as $stop) {
			$markers['###STOP_NAME###'] = $stop->stopPoint->name;
			$markers['###URL###'] = $this->pObj->pi_linkTP_keepPIvars_url(array('stopPointExternalCode' => $stop->stopPoint->externalCode));

			if (tx_icslibnavitia_Debug::IsDebugEnabled()) {
				$markers['###URL###'] .= '&' . $this->pObj->debug_param . '=' . t3lib_div::_GP($this->pObj->debugParam);
			}
			
			if($stopNum%2) {
				$markers['###DATA_THEME###'] = 'd';
			}
			else {
				$markers['###DATA_THEME###'] = 'e';
			}
	
			$stopListContent .= $this->pObj->cObj->substituteMarkerArray($stopListTemplate, $markers);
			$stopNum++;
		}
		
		$template = $this->pObj->cObj->substituteSubpart($template, '###STOP_LIST###', $stopListContent);
		$content .= $this->pObj->cObj->substituteMarkerArray($template, $markers);
		return $content;
	}
	
}