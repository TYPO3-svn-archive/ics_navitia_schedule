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
	
		$templatePart = $this->pObj->templates['lineList'];
		$lineList = $dataProvider->getLineList($networks);
		$lineTot = $lineList->Count();
		$template = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_SCHEDULE_LINE_LIST###');
		
		$markers = array(
			'###PREFIXID###' => $this->prefixId,
			'###LINE_LIST_TITLE###' => $this->pObj->pi_getLL('lineList.title'),
		);
		
		if ($lineTot) {
			foreach ($lineList->ToArray() as $line) {
				$aLines[$line->code] = $line;
			}
			
			ksort($aLines);
			
			$lineNum = 0;
			foreach ($aLines as $line) {
				
				if (!empty($line->name)) {
					$lineListTemplate = $this->pObj->cObj->getSubpart($templatePart, '###LINE_LIST###');
					$linePicto = $this->pObj->pictoLine->getlinepicto($line->code/*$line->externalCode*/, 'Navitia');
					
					$markers['###LINE_CODE###'] = $line->code . ' - ';
					
					if (!empty($linePicto)) {
						$markers['###LINE_PICTO###'] = $linePicto;
						$markers['###STYLE_LINECODE###'] = 'display:none;';
						$markers['###STYLE_LINEPICTO###'] = 'display:inline;';
					}
					else {
						$markers['###LINE_PICTO###'] = $line->code . ' - ';
						$markers['###STYLE_LINECODE###'] = 'display:inline;';
						$markers['###STYLE_LINEPICTO###'] = 'display:none;';
					}
					
					if($lineNum%2) {
						$markers['###DATA_THEME###'] = 'd';
					}
					else {
						$markers['###DATA_THEME###'] = 'e';
					}
					
					$markers['###LINE_NAME###'] = $line->name;
					$markers['###URL###'] = $this->pObj->pi_getPageLink($GLOBALS['TSFE']->id, '', array($this->pObj->prefixId . '[lineExternalCode]' => $line->externalCode));
					if (tx_icslibnavitia_Debug::IsDebugEnabled()) {
						$markers['###URL###'] .= '&' . $this->pObj->debug_param . '=' . t3lib_div::_GP($this->pObj->debugParam);
					}
					$lineListContent .= $this->pObj->cObj->substituteMarkerArray($lineListTemplate, $markers);
				}
				
				$lineNum++;
			}
			
		}
		$template = $this->pObj->cObj->substituteSubpart($template, '###LINE_LIST###', $lineListContent);
		$content .= $this->pObj->cObj->substituteMarkerArray($template, $markers);
		return $content;
	}
}
