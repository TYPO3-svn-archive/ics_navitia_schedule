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
 
class tx_icsnavitiaschedule_lineList {

	public function __construct($pObj) {
		$this->pObj = $pObj;
	}

	function getLineList($dataProvider, $networks = null) {
		
		//var_dump($networks);
	
		$templatePart = $this->pObj->templates['lineList'];
		$lineList = $dataProvider->getLineList($networks);
		$lineTot = $lineList->Count();
		$template = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_SCHEDULE_LINE_LIST###');
		
		$markers = array(
			'###PREFIXID###' => $this->prefixId,
			'###LINE_LIST_TITLE###' => $this->pObj->pi_getLL('lineList.title')
		);
		
		if ($lineTot) {
			// $lines = $lineList->ToArray();
			// usort($lines, create_function('$v1, $v2', 'if (intval($v1->code)) { if (intval($v2->code)) { } else {} } else { if (is_numeric($v2->code)) { } else {} }');
			foreach($lineList->ToArray() as $line)  {
				$aLines[$line->code] = $line;
			}
			
			ksort($aLines);
			
			if($this->pObj->debug) {
				$this->debugParam = t3lib_div::_GP($this->pObj->debug_param);
			}
			
			foreach($aLines as $line) {
				if(!empty($line->name)) {
					$lineListTemplate = $this->pObj->cObj->getSubpart($templatePart, '###LINE_LIST###');
					$linePicto = $this->pObj->pictoLine->getlinepicto($line->externalCode, 'Navitia');
					if(!empty($linePicto)) {
						$markers['###LINE_PICTO###'] = $linePicto;
					}
					else {
						$markers['###LINE_PICTO###'] = $line->code . ' - ';
					}
					
					$markers['###LINE_NAME###'] = $line->name;
					$markers['###URL###'] = $this->pObj->pi_getPageLink($GLOBALS['TSFE']->id, '', array($this->pObj->prefixId . '[lineExternalCode]' => $line->externalCode));
					if(!is_null($this->debugParam)) {
						$markers['###URL###'] .= '&' . $this->pObj->debug_param . '=' . $this->debugParam;
					}
					$lineListContent .= $this->pObj->cObj->substituteMarkerArray($lineListTemplate, $markers);
				}
			}
			
		}
		$template = $this->pObj->cObj->substituteSubpart($template, '###LINE_LIST###', $lineListContent);
		$content .= $this->pObj->cObj->substituteMarkerArray($template, $markers);
		return $content;
	}
	
}