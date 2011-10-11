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
 
class tx_icsnavitiaschedule_directionList {

	public function __construct($pObj) {
		$this->pObj = $pObj;
	}

	public function getDirectionList($dataProvider, $lineExternalCode) {
		$templatePart = $this->pObj->templates['directionList'];
		$line = $dataProvider->getLineByCode($lineExternalCode);
		
		$template = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_SCHEDULE_DIRECTION_LIST###');
		$markers = array(
			'###PREFIXID###' => $this->prefixId,
			'###DIRECTION_LIST_TITLE###' => $this->pObj->pi_getLL('directionList.title'),
		);
		$directionListContent = $this->getDirections($line);
		
		$template = $this->pObj->cObj->substituteSubpart($template, '###DIRECTION_LIST###', $directionListContent);
		$content .= $this->pObj->cObj->substituteMarkerArray($template, $markers);
		
		return $content;
	}
	
	private function getDirections($line) {
		$directionForward = $this->getDirection($line, true);
		$directionBackward = $this->getDirection($line, false);
		return $directionForward . $directionBackward;
	}
	
	private function getDirection($line, $forward = true) {
		$templatePart = $this->pObj->templates['directionList'];
		$template = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_SCHEDULE_DIRECTION_LIST###');
		$directionListTemplate = $this->pObj->cObj->getSubpart($template, '###DIRECTION_LIST###');
		
		$markers = array(
			'###PREFIXID###' => $this->pObj->prefixId,
			'###LINE_PICTO###' => $this->pObj->pictoLine->getlinepicto($line->externalCode, 'Navitia'),
		);
		
		if ($forward) {
			$markers['###DIRECTION_NAME###'] = $line->forward->name;
			$markers['###URL###'] = $this->pObj->pi_linkTP_keepPIvars_url(array('sens' => $forward));
			$markers['###DATA_THEME###'] = 'e';
		}
		else {
			$markers['###DIRECTION_NAME###'] = $line->backward->name;
			$markers['###URL###'] = $this->pObj->pi_linkTP_keepPIvars_url(array('sens' => 0));
			$markers['###DATA_THEME###'] = 'd';
		}

		if (tx_icslibnavitia_Debug::IsDebugEnabled()) {
			$markers['###URL###'] .= '&' . $this->pObj->debug_param . '=' . t3lib_div::_GP($this->pObj->debugParam);
		}
		
		$content = $this->pObj->cObj->substituteMarkerArray($directionListTemplate, $markers);
		return $content;
	}
	
}