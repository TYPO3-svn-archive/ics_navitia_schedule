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

/**
 * @author Pierrick Caillon <pierrick@in-cite.net>
 */
class tx_icsnavitiaschedule_nextDeparture {
	private $pObj;

	public function __construct($pObj) {
		$this->pObj = $pObj;
	}

	function renderNextDeparture($dataProvider, $lineExternalCode, $stopAreaExternalCode, $forward) {
		$templatePart = $this->pObj->templates['nextDeparture'];
		$template = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_STATION_NEXT###');
		
		$timeLimit = max(1, intval($this->pObj->conf['nextDeparture.']['nextLimit']));
		$dateChangeTime = intval($this->pObj->conf['nextDeparture.']['dateChangeTime']);
		$noNextDay = intval($this->pObj->conf['nextDeparture.']['noNextDay']);
		
		$data = $dataProvider->getNextDepartureByStopAreaForLine($stopAreaExternalCode, $lineExternalCode, $forward, $timeLimit, $dateChangeTime, $noNextDay);
		$line = $dataProvider->getLineByCode($lineExternalCode);
		
		$markers = array(
			'PREFIXID' => $this->pObj->prefixId,
		);
		
		if ($data->Count() > 0) {
			$markers['STATION'] = $this->makeStation($data, $line, $forward);
		}
		else {
			$station = $dataProvider->getStopAreaByCode($stopAreaExternalCode);
			$markers['STATION'] = $this->makeStationNoData($station, $line, $forward);
		}
		
		$content .= $this->pObj->cObj->substituteMarkerArray($template, $markers, '###|###');
		$content = $this->pObj->cObj->substituteMarkerArray($content, $markers, '###|###');
		return $content;
	}
	
	function makeStation(tx_icslibnavitia_INodeList $stopList, tx_icslibnavitia_Line $line, $forward) {
		$templatePart = $this->pObj->templates['nextDeparture'];
		$template = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_STATION###');

		$markers = array();
		$markers['PICTO'] = $this->pObj->pictoLine->getlinepicto($line->code/*$line->externalCode*/, 'Navitia');
		if (!$markers['PICTO']) $markers['PICTO'] = htmlspecialchars($line->code);
		$markers['LINE'] = htmlspecialchars($line->name);
		$markers['NAME'] = htmlspecialchars($stopList->Get(0)->stopArea->name);
		$direction = ($forward ? $line->forward : $line->backward);
		$markers['DIRECTION'] = htmlspecialchars($direction->name);
		
		$confBase = $this->pObj->conf['nextDeparture.'];
		
		foreach ($markers as $name => $value) {
			$markers[$name] = $this->pObj->cObj->stdWrap($value, $confBase[strtolower($name) . '_stdWrap.']);
		}
		
		$markers['TO_LABEL'] = htmlspecialchars($this->pObj->pi_getLL('nextDeparture_direction'));
		
		$timeTemplate = $this->pObj->cObj->getSubpart($template, '###TEMPLATE_TIMES###');
		$timeContent = '';
		for ($i = 0; $i < $stopList->Count(); $i++) {
			$stop = $stopList->Get($i);
			$timeMarkers = array();
			$time = mktime($stop->stopTime->hour, $stop->stopTime->minute, 0, date('m'), date('d'), date('Y'));
			$time = $this->pObj->cObj->stdWrap($time, $confBase['time_stdWrap.']);
			if ($stop->stopTime->Day && (($stop->stopTime->hour * 60 + $stop->stopTime->minute) >= intval($confBase['dateChangeTime']))) {
				$time = $this->pObj->cObj->stdWrap($time, $confBase['nextDay_stdWrap.']);
			}
			$timeMarkers['TIME'] = $time;
			$timeContent .= $this->pObj->cObj->substituteMarkerArray($timeTemplate, $timeMarkers, '###|###');
		}
		$template = $this->pObj->cObj->substituteSubpart($template, '###TEMPLATE_TIMES###', $timeContent);

		$content .= $this->pObj->cObj->substituteMarkerArray($template, $markers, '###|###');
		return $content;
	}
	
	function makeStationNoData(tx_icslibnavitia_StopArea $stopArea = null, tx_icslibnavitia_Line $line = null, $forward = true) {
		if ($stopArea == null) {
			return '(null)';
		}
		$templatePart = $this->pObj->templates['nextDeparture'];
		$template = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_STATION_NODATA###');

		$markers = array();
		$markers['PICTO'] = $this->pObj->pictoLine->getlinepicto($line->code/*$line->externalCode*/, 'Navitia');
		if (!$markers['PICTO']) $markers['PICTO'] = htmlspecialchars($line->code);
		$markers['LINE'] = htmlspecialchars($line->name);
		$markers['NAME'] = htmlspecialchars($stopArea->name);
		$direction = ($forward ? $line->forward : $line->backward);
		$markers['DIRECTION'] = htmlspecialchars($direction->name);
		
		$confBase = $this->pObj->conf['nextDeparture.'];
		
		foreach ($markers as $name => $value) {
			$markers[$name] = $this->pObj->cObj->stdWrap($value, $confBase[strtolower($name) . '_stdWrap.']);
		}
		
		$markers['TO_LABEL'] = htmlspecialchars($this->pObj->pi_getLL('nextDeparture_direction'));
		
		$markers['MESSAGE'] = htmlspecialchars($this->pObj->pi_getLL('nextDeparture_noData'));

		$content .= $this->pObj->cObj->substituteMarkerArray($template, $markers, '###|###');
		return $content;
	}
}