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
 
class tx_icsnavitiaschedule_departureBoard {

	public function __construct($pObj) {
		$this->pObj = $pObj;
	}

	function renderDepartureBoard($dataProvider, $lineExternalCode, $forward = true, $stopPointExternalCode) {
		$templatePart = $this->pObj->templates['departureBoard'];
		$template = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_SCHEDULE_TABLE###');
		
		//$line = $dataProvider->getLineByCode($lineExternalCode);
		$hours = array();
		$aHours = false;
		$hoursToShowContent = '';
		$templateLineContent = '';
		$lineContent = '';
		
		//$currentHour = date('H');
		//$currentHour = 17;
		
		if(!isset($this->pObj->piVars['hourOffset'])) {
			$currentOffset = 0;
		}
		else {
			$currentOffset = $this->pObj->piVars['hourOffset'];
		}
		
		if(empty($this->pObj->piVars['date'])) {
			$currentDate = date('d/m/Y');
		}
		else {
			$currentDate = $this->pObj->piVars['date'];
		}
		
		if(empty($this->pObj->piVars['hour'])) {
			$currentHour = date('H');
			$currentTime = date('H') . 'h' . date('i');
		}
		else {
			$aTime = explode('h', $this->pObj->piVars['hour']);
			$currentHour = $aTime[0];
			$currentTime = $this->pObj->piVars['hour'];
		}
		
		$data = $dataProvider->getDepartureBoardByStopPointForLine($stopPointExternalCode, $lineExternalCode, new DateTime('now'), true);
		$aLines = $data['LineList']->ToArray();
		$line = $aLines[0];
		
		//$aStopPoint = $data['StopPointList']->ToArray();
		
		//var_dump($data['StopPointList']->count());
		
		/*foreach($data['StopPointList']->ToArray() as $stopPoint) {
			var_dump($stopPoint->name);
		}*/
		
		if($this->pObj->debug) {
			$this->debugParam = t3lib_div::_GP($this->pObj->debug_param);
		}
		
		$markers = array(
			'###PREFIXID###' => $this->pObj->prefixId,
			'###LINE_PICTO###' => $this->pObj->pictoLine->getlinepicto($line->externalCode, 'Navitia'),
			'###DATE_SEL###' => $currentDate, // format date à mettre en conf
			'###HOUR_SEL###' => $currentTime, // format heure à mettre en conf
			'###HOUR_LESS_TEXT###' => $this->pObj->pi_getLL('hourLess'),
			'###HOUR_MORE_TEXT###' => $this->pObj->pi_getLL('hourMore'),
			'###HOUR###' => '',
			'###HOUR_LESS_URL###' => $this->pObj->pi_linkTP_keepPIvars_url(array('hourOffset' => $currentOffset-1)),
			'###HOUR_MORE_URL###' => $this->pObj->pi_linkTP_keepPIvars_url(array('hourOffset' => $currentOffset+1)),
			'###ACTION_URL###' => $this->pObj->pi_linkTP_keepPIvars_url(array('hourOffset' => null)),
		);
		
		if(!is_null($this->debugParam)) {
			$markers['###HOUR_LESS_URL###'] .= '&' . $this->pObj->debug_param . '=' . $this->debugParam;
			$markers['###HOUR_MORE_URL###'] .= '&' . $this->pObj->debug_param . '=' . $this->debugParam;
			$markers['###ACTION_URL###'] .= '&' . $this->pObj->debug_param . '=' . $this->debugParam;
		}
		
		if($forward) {
			$markers['###DIRECTION_NAME###'] = $line->forward->name;
		}
		else {
			$markers['###DIRECTION_NAME###'] = $line->backward->name;
		}
		
		$stopPoint = $data['StopPointList']->ToArray();
		
		$markers['###STOP_NAME###'] = $stopPoint[0]->name;
		
		foreach($data['StopList']->ToArray() as $stop) {
			if(!in_array($stop->stopTime->hour, $hours)) {
				$hours[] = $stop->stopTime->hour;
			}
			$schedules[$stop->stopTime->hour][] = $stop->stopTime->minute;
		}
		
		if(is_array($hours) && count($hours)) {
			foreach($hours as $index => $hour) {
			
				if(isset($this->pObj->piVars['hourOffset']) && !empty($this->pObj->piVars['hourOffset'])) {
					$index += $this->pObj->piVars['hourOffset'];
				}
			
				if(($hour >= $currentHour) && !$aHours) {
					
					if(intval($index-1)>=0 && !is_null($hours[intval($index-2)])) {
						$hourToShow[] = $hours[intval($index-1)]; // On récupère l'heure juste avant l'heure courante
					}
					
					if(!is_null($hours[$index])) {
						$hourToShow[] = $hours[$index]; // On récupère la 1ère heure par rapport à l'heure courante où on a des horaires
					}
					
					if(intval($index+1)<=intval(count($hours)-1) && !is_null($hours[intval($index+1)])) {
						$hourToShow[] = $hours[intval($index+1)]; // On récupère la 2nde heure par rapport à l'heure courante où on a des horaires
					}
					
					if(intval($index+2)<=intval(count($hours)-1) && !is_null($hours[intval($index+2)])) {
						$hourToShow[] = $hours[intval($index+2)]; // On récupère la 3ème heure par rapport à l'heure courante où on a des horaires
					}
					$aHours = true;
				}
				elseif(!$aHours && $hour == $hours[count($hours)-1] && intval($hour+1) == $currentHour) { // le cas où on a pas d'horaires mais seulement pour h-1
					if(!is_null($hours[$index])) {
						$hourToShow[] = $hours[$index]; 
					}
				}
			}
		}
		
		if(!$aHours) {
			//var_dump($hours);
			//var_dump($index);
			/*if(intval($index-1)>=0 && !is_null($hours[intval($index-2)])) {
				$hourToShow[] = $hours[intval($index-1)]; // On récupère l'heure juste avant l'heure courante
			}
			var_dump($hours);*/
		}
		//array_pop($hours)
		
		//var_dump(count($hourToShow));
		//var_dump($hourToShow);

		if(is_array($hourToShow) && count($hourToShow)) {
			foreach($hourToShow as $hour) {
				$hoursToShowTemplate = $this->pObj->cObj->getSubpart($template, '###HOUR_COLUMNS###');
				$markers['###HOUR###'] = $hour;
				$hoursToShowContent .= $this->pObj->cObj->substituteMarkerArray($hoursToShowTemplate, $markers);
			}
			$template = $this->pObj->cObj->substituteSubpart($template, '###HOUR_COLUMNS###', $hoursToShowContent);
			
			if(is_array($schedules) && count($schedules)) {
				foreach($schedules as $schedule) {
					$aNbLines[] = count($schedule);
				}
				$nbLines = max($aNbLines);
			}
		}

		if($nbLines) {
			$templateLine = $this->pObj->cObj->getSubpart($template, '###SCHEDULES_LINE###');
			for($index=0;$index<$nbLines;$index++) {
				
				$minsContent = '';
				foreach($hourToShow as $hour) {
					$minutesTemplate = $this->pObj->cObj->getSubpart($templateLine, '###MIN_COLUMNS###');

					if($index<count($schedules[$hour])) {
						$markers['###MIN###'] = $schedules[$hour][$index];
					}
					else {
						$markers['###MIN###'] = '';
					}
					$minsContent .= $this->pObj->cObj->substituteMarkerArray($minutesTemplate, $markers);
				}
				$lineContent .= $this->pObj->cObj->substituteSubpart($templateLine, '###MIN_COLUMNS###', $minsContent);
			}
		}

		$template = $this->pObj->cObj->substituteSubpart($template, '###SCHEDULES_LINE###', $lineContent);
		$content .= $this->pObj->cObj->substituteMarkerArray($template, $markers);
		return $content;
	}
	
}