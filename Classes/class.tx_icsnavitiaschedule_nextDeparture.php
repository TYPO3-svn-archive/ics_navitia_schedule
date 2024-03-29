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

/**
 * @author Pierrick Caillon <pierrick@in-cite.net>
 */
class tx_icsnavitiaschedule_nextDeparture {
	private $pObj;

	public function __construct($pObj) {
		$this->pObj = $pObj;
	}

	public function renderNextDeparture(tx_icslibnavitia_APIService $dataProvider, $lineExternalCode, $stopAreaExternalCode, $forward) {
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
	
	public function renderProximity(tx_icslibnavitia_APIService $dataProvider) {
		$templatePart = $this->pObj->templates['nextDeparture'];
		$template = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_PROXIMITY###');
		
		$markers = array(
			'PREFIXID'		=> $this->pObj->prefixId,
			'TITLE'			=> htmlspecialchars($this->pObj->pi_getLL('title')),
		);
		
		$geoloc = new tx_icslibgeoloc_GeoLocation();
		if ($geoloc->Position === false) {
			$template = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_PROXIMITY_NODATA###');
			$markers['MESSAGE'] = htmlspecialchars($this->pObj->pi_getLL('nextDeparture_noLocation'));
			if($GLOBALS['TSFE']->tmpl->setup['geoloc.']['file']) {
				$geolocImg = array(
					'file' => $GLOBALS['TSFE']->tmpl->setup['geoloc.']['file']
				);
				$markers['ICON'] = $this->pObj->cObj->IMAGE($geolocImg);
				$markers['BR'] = '<br />';
			}
		}
		else {
			$timeLimit = max(1, intval($this->pObj->conf['nextDeparture.']['nextLimit']));
			$dateChangeTime = intval($this->pObj->conf['nextDeparture.']['dateChangeTime']);
			$noNextDay = intval($this->pObj->conf['nextDeparture.']['noNextDay']);
			$confBase = $this->pObj->conf['nextDeparture.']['proximity.'];
			$resultsLimit = $confBase['max'];
			if (($this->pObj->flexLimit !== false) && ($this->pObj->flexLimit > 0))
				$resultsLimit = $this->pObj->flexLimit;
			$coords = t3lib_div::makeInstance('tx_icslibnavitia_Coord');
			$loc = $geoloc->Position;
			$coords->lat = $loc['latitude'];
			$coords->lng = $loc['longitude'];
			$proximities = $dataProvider->getStopAreaProximityList($coords, $confBase['distance'], $confBase['min'], $resultsLimit);
			
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
	
	public function makeStation(tx_icslibnavitia_INodeList $stopList, tx_icslibnavitia_Line $line, $forward, $distance = false, $noTime = false) {
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
		$markers['PICTO'] = ($this->pObj->pictoLine != null) ? $this->pObj->pictoLine->getlinepicto($line->code/*$line->externalCode*/, 'Navitia') : '';
		if (!$markers['PICTO']) $markers['PICTO'] = htmlspecialchars($line->code);
		$markers['LINE'] = htmlspecialchars($line->name);
		$markers['NAME'] = htmlspecialchars($stopList->Get(0)->stopPoint->stopArea->name);
		$markers['DIRECTION'] = htmlspecialchars($direction->name);
		$markers['STOP_LABEL'] = $this->pObj->pi_getLL('stoppoint');
		$confBase = $this->pObj->conf['nextDeparture.'];
		
		foreach ($markers as $name => $value) {
			$markers[$name] = $localCObj->stdWrap($value, $confBase[strtolower($name) . '_stdWrap.']);
		}
		
		if ($distance !== false) {
			$markers['DISTANCE'] = $localCObj->stdWrap($distance, $confBase['proximity.']['distance_stdWrap.']);
		}
		
		$markers['TO_LABEL'] = htmlspecialchars($this->pObj->pi_getLL('nextDeparture_direction'));
		
		$timeContent = '';
		if (!$noTime) {
			$timeTemplate = $localCObj->getSubpart($template, '###TEMPLATE_TIMES###');
			$currentTime = $_SERVER['REQUEST_TIME'];
			$dateChangeTime = intval($confBase['dateChangeTime']);
			$currentMinutes = (int)date('H') * 60 + (int)date('i');
			$limit = (int)$confBase['timeSpanLimit'];
			for ($i = 0; $i < $stopList->Count(); $i++) {
				$stop = $stopList->Get($i);
				$timeMarkers = array();
				$time = mktime($stop->stopTime->hour, $stop->stopTime->minute, 0, date('m'), date('d') + $stop->stopTime->day, date('Y'));
				$minutes = $stop->stopTime->hour * 60 + $stop->stopTime->minute;
				$modifiedTime = t3lib_div::makeInstance('tx_icslibnavitia_Time');
				$modifiedTime->totalSeconds = $stop->stopTime->totalSeconds + (($minutes < $dateChangeTime) ? (86400) : (0)) - $dateChangeTime * 60;
				$day = $modifiedTime->day;
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
					if ($day > 0) {
						$time = sprintf($localItemCObj->stdWrap($time, $confBase['nextDay_stdWrap.']), $day);
					}
				}
				$timeMarkers['TIME'] = $time;
				$timeContent .= $localItemCObj->stdWrap(
					$localItemCObj->substituteMarkerArray($timeTemplate, $timeMarkers, '###|###'),
					$confBase['timeItem_stdWrap.']
				);
			}
		}
		$template = $localCObj->substituteSubpart($template, '###TEMPLATE_TIMES###', $timeContent);

		$content .= $localCObj->stdWrap(
			$this->pObj->cObj->substituteMarkerArray($template, $markers, '###|###'),
			$confBase['stationItem_stdWrap.']
		);
		return $content;
	}
	
	public function makeStationNoData(tx_icslibnavitia_StopArea $stopArea = null, tx_icslibnavitia_Line $line = null, $forward = true, $noTime = false) {
		if ($stopArea == null) {
			return '(null)';
		}
		$templatePart = $this->pObj->templates['nextDeparture'];
		$template = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_STATION_NODATA###');
		$direction = ($forward ? $line->forward : $line->backward);

		$cObjData = array(
			'lineCode' => $line->code,
			'lineName' => $line->name,
			'stopName' => $stopArea->name,
			'direction' => $direction->name,
			'noData' => 1,
		);
		$localCObj = t3lib_div::makeInstance('tslib_cObj');
		$localCObj->start($cObjData);

		$markers = array();
		$markers['PICTO'] = ($this->pObj->pictoLine != null) ? $this->pObj->pictoLine->getlinepicto($line->code/*$line->externalCode*/, 'Navitia') : '';
		if (!$markers['PICTO']) $markers['PICTO'] = htmlspecialchars($line->code);
		$markers['LINE'] = htmlspecialchars($line->name);
		$markers['NAME'] = htmlspecialchars($stopArea->name);
		$markers['DIRECTION'] = htmlspecialchars($direction->name);
		
		$confBase = $this->pObj->conf['nextDeparture.'];
		
		foreach ($markers as $name => $value) {
			$markers[$name] = $localCObj->stdWrap($value, $confBase[strtolower($name) . '_stdWrap.']);
		}
		
		$markers['TO_LABEL'] = htmlspecialchars($this->pObj->pi_getLL('nextDeparture_direction'));
		
		$markers['MESSAGE'] = $noTime ? '' : htmlspecialchars($this->pObj->pi_getLL('nextDeparture_noData'));

		$content .= $localCObj->stdWrap(
			$this->pObj->cObj->substituteMarkerArray($template, $markers, '###|###'),
			$confBase['stationItem_stdWrap.']
		);
		return $content;
	}
	
	public function renderBookmarks(tx_icslibnavitia_APIService $dataProvider, $edit = false) {
		if (!t3lib_extMgm::isLoaded('ics_bookmarks'))
			return '';
		$content = '';
		
		$timeLimit = max(0, intval($this->pObj->conf['nextDeparture.']['nextLimit']));
		$dateChangeTime = intval($this->pObj->conf['nextDeparture.']['dateChangeTime']);
		$noNextDay = intval($this->pObj->conf['nextDeparture.']['noNextDay']);
		$resultsLimit = max(0, intval($this->pObj->conf['nextDeparture.']['resultsLimit']));
		if ($this->pObj->flexLimit !== false)
			$resultsLimit = $this->pObj->flexLimit;
		$hideEmpty = $this->pObj->conf['nextDeparture.']['bookmarks.']['hideEmpty'];
	
		$templatePart = $this->pObj->templates['nextDeparture'];
		if ($edit) {
			$template = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_BOOKMARK_MANAGE###');
		}
		else {
			$template = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_BOOKMARK###');
		}
		
		$libsBookmarks = t3lib_div::makeInstance('tx_icsbookmarks_libs');
		$bookmarks = $libsBookmarks->getBookmarks($this->pObj->extKey);
		$bookmarks = array_flip($bookmarks);
		ksort($bookmarks);
		
		$markers = array(
			'PREFIXID' => $this->pObj->prefixId,
			'BOOKMARKS' => $this->pObj->pi_getLL('bookmarks'),
		);
		
		$count = ($edit || !$resultsLimit) ? count($bookmarks) : min($resultsLimit, count($bookmarks));
		$stationTemplate = $this->pObj->cObj->getSubpart($template, '###STATIONS_LIST###');
		$stationsContent = '';
		for ($i = 0; $i < $count; $i++) {
			$bookmarkData = unserialize($bookmarks[$i]);
			
			$line = $dataProvider->getLineByCode($bookmarkData['lineExternalCode']);
			$data = $dataProvider->getNextDepartureByStopAreaForLine($bookmarkData['stopAreaExternalCode'], $bookmarkData['lineExternalCode'], $bookmarkData['forward'], $timeLimit, $dateChangeTime, $noNextDay);
			
			$stationMarkers = array();
			if ($data->Count() > 0) {
				$stationMarkers['STATION'] = $this->makeStation($data, $line, $bookmarkData['forward'], false, $edit);
			}
			else {
				if ($hideEmpty && !$edit) {
					if ($resultsLimit && ($count < count($bookmarks))) {
						$count++;
					}
					continue;
				}
				$stopArea = $dataProvider->getStopAreaByCode($bookmarkData['stopAreaExternalCode']);
				$stationMarkers['STATION'] = $this->makeStationNoData($stopArea, $line, $bookmarkData['forward'], $edit);
			}
			
			if ($edit) {
				
				$upIcon = array(
					'file'			=> $this->pObj->conf['icons.']['up'],
					'titleText'		=> htmlspecialchars($this->pObj->pi_getLL('bookmarks_up')),
					'altText'		=> htmlspecialchars($this->pObj->pi_getLL('bookmarks_up'))
				);

				$downIcon = array(
					'file'			=> $this->pObj->conf['icons.']['down'],
					'titleText'		=> htmlspecialchars($this->pObj->pi_getLL('bookmarks_down')),
					'altText'		=> htmlspecialchars($this->pObj->pi_getLL('bookmarks_down'))
				);
				
				$deleteIcon = array(
					'file'			=> $this->pObj->conf['icons.']['delete'],
					'titleText'		=> htmlspecialchars($this->pObj->pi_getLL('bookmarks_delete')),
					'altText'		=> htmlspecialchars($this->pObj->pi_getLL('bookmarks_delete'))
				);
			
				$redirect = t3lib_div::getIndpEnv('TYPO3_REQUEST_URL');
				$markerLinks['DELETE_LINK'] = htmlspecialchars($libsBookmarks->getURL(tx_icsbookmarks_libs::ACTION_DELETE, $this->pObj->extKey, $i, $redirect));
				$markerLinks['UP_LINK'] = htmlspecialchars($libsBookmarks->getURL(tx_icsbookmarks_libs::ACTION_UP, $this->pObj->extKey, $i, $redirect));
				$markerLinks['DOWN_LINK'] = htmlspecialchars($libsBookmarks->getURL(tx_icsbookmarks_libs::ACTION_DOWN, $this->pObj->extKey, $i, $redirect));
				$markerLinks['UP_LABEL'] = $this->pObj->cObj->IMAGE($upIcon);
				$markerLinks['DELETE_LABEL'] = $this->pObj->cObj->IMAGE($deleteIcon);
				$markerLinks['DOWN_LABEL'] = $this->pObj->cObj->IMAGE($downIcon);
				
				$templateStationLinks = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_BOOKMARK_LINKS###');
				
				if ($i == 0)
					$templateStationLinks = $this->pObj->cObj->substituteSubpart($templateStationLinks, '###UP###', '');
				if ($i == count($bookmarks) - 1)
					$templateStationLinks = $this->pObj->cObj->substituteSubpart($templateStationLinks, '###DOWN###', '');
				
				$stationLinks = $this->pObj->cObj->substituteMarkerArray($templateStationLinks, $markerLinks, '###|###');
				$stationMarkers['LINKS'] = $stationLinks;
			}
			$stationsContent .= $this->pObj->cObj->substituteMarkerArray($stationTemplate, $stationMarkers, '###|###');
		}
		
		if ($stationsContent) {
			$content = $this->pObj->cObj->substituteSubpart($template, '###STATIONS_LIST###', $stationsContent);
		}
		else {
			if ($this->pObj->conf['nextDeparture.']['bookmarks.']['showEmpty']) {
				$content = $this->pObj->cObj->stdWrap($this->pObj->pi_getLL('noData'), $this->pObj->conf['nextDeparture.']['bookmarks.']['noData_stdWrap.']);
			}
		}
		
		$content = $this->pObj->cObj->substituteMarkerArray($content, $markers, '###|###');
		return $content;
	}
}