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

	public function renderNextDeparture($dataProvider, $lineExternalCode, $stopAreaExternalCode, $forward) {
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
	
	public function renderProximity($dataProvider) {
		$templatePart = $this->pObj->templates['nextDeparture'];
		$template = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_PROXIMITY###');

		$markers = array(
			'PREFIXID' => $this->pObj->prefixId,
		);
		
		$geoloc = new tx_icslibgeoloc_GeoLocation();
		if ($geoloc->Position === false) {
			$template = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_PROXIMITY_NODATA###');
			$markers['MESSAGE'] = htmlspecialchars($this->pObj->pi_getLL('nextDeparture_noLocation'));
		}
		else {
			$timeLimit = max(1, intval($this->pObj->conf['nextDeparture.']['nextLimit']));
			$dateChangeTime = intval($this->pObj->conf['nextDeparture.']['dateChangeTime']);
			$noNextDay = intval($this->pObj->conf['nextDeparture.']['noNextDay']);
			$confBase = $this->pObj->conf['nextDeparture.']['proximity.'];
			$coords = t3lib_div::makeInstance('tx_icslibnavitia_Coord');
			$loc = $geoloc->Position;
			$coords->lat = $loc['latitude'];
			$coords->lng = $loc['longitude'];
			$proximities = $dataProvider->getStopAreaProximityList($coords, $confBase['distance'], $confBase['min'], $confBase['max']);
			$stationsTemplate = $this->pObj->cObj->getSubpart($template, '###STATIONS###');
			$stationsContent = '';
			$networks = $this->pObj->getNetworkList();
			for ($i = 0; $i < $proximities->Count(); $i++) {
				$stopArea = $proximities->Get($i)->stopArea;
				$lines = $dataProvider->getLineListByStopAreaCode($stopArea->externalCode, $networks);
				for ($j = 0; $j < $lines->Count(); $j++) {
					$line = $lines->Get($j);
					foreach (array(true, false) as $forward) {
						$stationsMarkers = array();
						$data = $dataProvider->getNextDepartureByStopAreaForLine($stopArea->externalCode, $line->externalCode, $forward, $timeLimit, $dateChangeTime, $noNextDay);
						if ($data->Count() > 0) {
							$stationsMarkers['STATION'] = $this->makeStation($data, $line, $forward, $proximities->Get($i)->distance);
						}
						else {
							if ($confBase['hideEmpty'])
								continue;
							$stationsMarkers['STATION'] = $this->makeStationNoData($stopArea, $line, $forward);
						}
						$stationsContent .= $this->pObj->cObj->substituteMarkerArray($stationsTemplate, $stationsMarkers, '###|###');
					}
				}
			}
			$template = $this->pObj->cObj->substituteSubpart($template, '###STATIONS###', $stationsContent);
		}

		$content .= $this->pObj->cObj->substituteMarkerArray($template, $markers, '###|###');
		$content = $this->pObj->cObj->substituteMarkerArray($content, $markers, '###|###');
		return $content;
	}
	
	public function makeStation(tx_icslibnavitia_INodeList $stopList, tx_icslibnavitia_Line $line, $forward, $distance = false) {
		$templatePart = $this->pObj->templates['nextDeparture'];
		$template = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_STATION###');
		if ($distance !== false) {
			$template = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_STATION_PROXIMITY###');
		}
		$direction = ($forward ? $line->forward : $line->backward);
		
		$cObjData = array(
			'lineCode' => $line->code,
			'lineName' => $line->name,
			'stopName' => $stopList->Get(0)->stopPoint->stopArea->name,
			'direction' => $direction->name
		);
		if ($distance !== false)
			$cObjData['distance'] = $distance;
		$localCObj = t3lib_div::makeInstance('tslib_cObj');
		$localCObj->start($cObjData);

		$markers = array();
		$markers['PICTO'] = $this->pObj->pictoLine->getlinepicto($line->code/*$line->externalCode*/, 'Navitia');
		if (!$markers['PICTO']) $markers['PICTO'] = htmlspecialchars($line->code);
		$markers['LINE'] = htmlspecialchars($line->name);
		$markers['NAME'] = htmlspecialchars($stopList->Get(0)->stopPoint->stopArea->name);
		$markers['DIRECTION'] = htmlspecialchars($direction->name);
		
		$confBase = $this->pObj->conf['nextDeparture.'];
		
		foreach ($markers as $name => $value) {
			$markers[$name] = $localCObj->stdWrap($value, $confBase[strtolower($name) . '_stdWrap.']);
		}
		
		if ($distance !== false) {
			$markers['DISTANCE'] = $localCObj->stdWrap($distance, $confBase['proximity.']['distance_stdWrap.']);
		}
		
		$markers['TO_LABEL'] = htmlspecialchars($this->pObj->pi_getLL('nextDeparture_direction'));
		
		$timeTemplate = $localCObj->getSubpart($template, '###TEMPLATE_TIMES###');
		$timeContent = '';
		$currentTime = $_SERVER['REQUEST_TIME'];
		$dateChangeTime = intval($confBase['dateChangeTime']);
		$currentMinutes = (int)date('H') * 60 + (int)date('i');
		$limit = (int)$confBase['timeSpanLimit'];
		for ($i = 0; $i < $stopList->Count(); $i++) {
			$stop = $stopList->Get($i);
			$timeMarkers = array();
			$time = mktime($stop->stopTime->hour, $stop->stopTime->minute, 0, date('m'), date('d'), date('Y'));
			$day = $stop->stopTime->day;
			$minutes = $stop->stopTime->hour * 60 + $stop->stopTime->minute;
			// if (($dateChangeTime > 0) && ($currentMinutes < $dateChangeTime) && ($minutes >= $dateChangeTime))
				// $day++;
			$cObjDataItem = array(
				'time' => $time,
				'day' =>  $day,
				'minuteSpan' => totalSeconds / 60 - $currentMinutes,
			);
			$localItemCObj = t3lib_div::makeInstance('tslib_cObj');
			$localItemCObj->start($cObjDataItem);
			$localItemCObj->setParent($cObjData, '');
			if ($limit && !$day && (($minutes - $currentMinutes) < $limit))
				$time = $localItemCObj->stdWrap(($minutes - $currentMinutes), $confBase['timeSpan_stdWrap.']);
			else {
				$time = $localItemCObj->stdWrap($time, $confBase['time_stdWrap.']);
				if ($day) {
					$time = sprintf($localItemCObj->stdWrap($time, $confBase['nextDay_stdWrap.']), $day);
				}
			}
			$timeMarkers['TIME'] = $time;
			$timeContent .= $localItemCObj->stdWrap(
				$localItemCObj->substituteMarkerArray($timeTemplate, $timeMarkers, '###|###'),
				$confBase['timeItem_stdWrap.']
			);
		}
		$template = $localCObj->substituteSubpart($template, '###TEMPLATE_TIMES###', $timeContent);

		$content .= $localCObj->stdWrap(
			$this->pObj->cObj->substituteMarkerArray($template, $markers, '###|###'),
			$confBase['stationItem_stdWrap.']
		);
		return $content;
	}
	
	public function makeStationNoData(tx_icslibnavitia_StopArea $stopArea = null, tx_icslibnavitia_Line $line = null, $forward = true) {
		if ($stopArea == null) {
			return '(null)';
		}
		$templatePart = $this->pObj->templates['nextDeparture'];
		$template = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_STATION_NODATA###');
		$direction = ($forward ? $line->forward : $line->backward);

		$cObjData = array(
			'lineCode' => $line->code,
			'lineName' => $line->name,
			'stopName' => $stopList->Get(0)->stopPoint->stopArea->name,
			'direction' => $direction->name,
			'noData' => 1,
		);
		$localCObj = t3lib_div::makeInstance('tslib_cObj');
		$localCObj->start($cObjData);

		$markers = array();
		$markers['PICTO'] = $this->pObj->pictoLine->getlinepicto($line->code/*$line->externalCode*/, 'Navitia');
		if (!$markers['PICTO']) $markers['PICTO'] = htmlspecialchars($line->code);
		$markers['LINE'] = htmlspecialchars($line->name);
		$markers['NAME'] = htmlspecialchars($stopArea->name);
		$markers['DIRECTION'] = htmlspecialchars($direction->name);
		
		$confBase = $this->pObj->conf['nextDeparture.'];
		
		foreach ($markers as $name => $value) {
			$markers[$name] = $localCObj->stdWrap($value, $confBase[strtolower($name) . '_stdWrap.']);
		}
		
		$markers['TO_LABEL'] = htmlspecialchars($this->pObj->pi_getLL('nextDeparture_direction'));
		
		$markers['MESSAGE'] = htmlspecialchars($this->pObj->pi_getLL('nextDeparture_noData'));

		$content .= $localCObj->stdWrap(
			$this->pObj->cObj->substituteMarkerArray($template, $markers, '###|###'),
			$confBase['stationItem_stdWrap.']
		);
		return $content;
	}
	
	public function renderBookmarks($dataProvider, $limit = 5) {
		$content = '';
		if (t3lib_extMgm::isLoaded('ics_bookmarks')) {
			$templatePart = $this->pObj->templates['nextDeparture'];
			$template = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_BOOKMARK###');
			
			$libsBookmarks = t3lib_div::makeInstance('tx_icsbookmarks_libs');
			$bookmarks = $libsBookmarks->getBookmarks($this->pObj->extKey);
			
			$bookmarks = array_flip($bookmarks);
			
			$markers = array(
				'PREFIXID' => $this->pObj->prefixId,
				'BOOKMARKS' => $this->pObj->pi_getLL('bookmarks'),
			);
			
			if(is_array($bookmarks) && count($bookmarks)) {
				foreach($bookmarks as $sorting => $bookmark) {
					$templateStation = $this->pObj->cObj->getSubpart($templatePart, '###STATIONS_LIST###');
					$aBookmarsData = unserialize($bookmark);
					
					//TODO $limit FF
					$timeLimit = max(1, intval($this->pObj->conf['nextDeparture.']['nextLimit']));
					$dateChangeTime = intval($this->pObj->conf['nextDeparture.']['dateChangeTime']);
					$noNextDay = intval($this->pObj->conf['nextDeparture.']['noNextDay']);
					$confBase = $this->pObj->conf['nextDeparture.']['proximity.'];
					
					$line = $dataProvider->getLineByCode($aBookmarsData['lineExternalCode']);
					$data = $dataProvider->getNextDepartureByStopAreaForLine($aBookmarsData['stopAreaExternalCode'], $aBookmarsData['lineExternalCode'], $aBookmarsData['forward'], $timeLimit, $dateChangeTime, $noNextDay);
					
					
					$stationsMarkers = array();
					if ($data->Count() > 0) {
						$markers['STATION'] = $this->makeStation($data, $line, $aBookmarsData['forward']);
					}
					else {
						if ($confBase['hideEmpty'])
							continue;
						$stopArea = $dataProvider->getStopAreaByCode($aBookmarsData['stopAreaExternalCode']);
						$markers['STATION'] = $this->makeStationNoData($stopArea, $line, $forward);
					}
					$contentStation .= $this->pObj->cObj->substituteMarkerArray($templateStation, $markers, '###|###');
				}
			}
			
			$content = $this->pObj->cObj->substituteSubpart($template, '###STATIONS_LIST###', $contentStation);
			$content = $this->pObj->cObj->substituteMarkerArray($content, $markers, '###|###');
		}
		return $content;
	}
}