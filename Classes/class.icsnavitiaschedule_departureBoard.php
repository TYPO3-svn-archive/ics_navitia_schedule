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

	private $aDestination = array(
		-1		=> '',
		0		=> 'a',
		1		=> 'b',
		2		=> 'c',
		3		=> 'd',
	);

	public function __construct($pObj) {
		$this->pObj = $pObj;
	}

	public function renderDepartureBoard($dataProvider, $lineExternalCode, $forward, $stopPointExternalCode) {
		$templatePart = $this->pObj->templates['departureBoard'];
		$template = $this->pObj->cObj->getSubpart($templatePart, '###TEMPLATE_SCHEDULE_TABLE###');

		if(is_array($this->pObj->conf['departureBoard.']['destination.'])) {
			$libsBookmarks->aDestination = $this->pObj->conf['departureBoard.']['destination.'];
		}
		
		$hours = array();
		$aHours = false;
		$hoursToShowContent = '';
		$templateLineContent = '';
		$lineContent = '';
		$destinationContent = '';
		
		if (!isset($this->pObj->piVars['hourOffset'])) {
			$currentOffset = 0;
		}
		else {
			$currentOffset = $this->pObj->piVars['hourOffset'];
		}
		
		$currentDateFormatted = new DateTime();
		if (empty($this->pObj->piVars['date'])) {
			//setlocale (LC_TIME, 'fr_FR.utf8','fra');
			//$currentDate = strftime('%A %d %B %Y'); // TODO: TypoScript setting.
			
			$mktimeDate = mktime(date('H') + $currentOffset, date('i'), 0, date('m'), date('d'), date('Y'));
			$currentDate = date('d/m/Y', $mktimeDate); // TODO: TypoScript setting.
			$aDate = explode('/', date('d/m/Y', $mktimeDate));
			$currentDateFormatted->setDate(date('Y', $mktimeDate), date('m', $mktimeDate), date('d', $mktimeDate));
		}
		else {
			
			$aDate = explode('/',$this->pObj->piVars['date']);
			
			if($this->pObj->piVars['hour']) {
				$aTime = explode('h', $this->pObj->piVars['hour']);
				$hour = $aTime[0];
				$minute = $aTime[1];
			}
			else {
				$hour = date('H');
				$minute = date('i');
			}
			
			$mktimeDate = mktime($hour + $currentOffset, $minute, 0, $aDate[1], $aDate[0], $aDate[2]);
			$currentDate = date('d/m/Y', $mktimeDate);
			
			//$mktimeDate = mktime(date('H') + $currentOffset, date('i'), 0, $aDate[1], $aDate[0], $aDate[2]);
			$currentDateFormatted->setDate(date('Y', $mktimeDate), date('m', $mktimeDate), date('d', $mktimeDate));
		}
		
		if (empty($this->pObj->piVars['hour'])) {
			
			//$currentTime = date('H\\hi');
			$mktimeDate = mktime(date('H') + $currentOffset, date('i'), 0, $aDate[1], $aDate[0], $aDate[2]);
			$currentHour = date('H', $mktimeDate);	// TODO: TypoScript setting.
			$currentTime = intval(date('H', $mktimeDate)) . 'h' .  date('i', $mktimeDate);
			//$currentTime = intval(date('H')) + $currentOffset . 'h' .  date('i');
		}
		else {
			$aTime = explode('h', $this->pObj->piVars['hour']);
			$mktimeDate = mktime($aTime[0] + $currentOffset, $aTime[1], 0, $aDate[1], $aDate[0], $aDate[2]);
			$currentHour = intval(date('H', $mktimeDate));
			$currentTime = intval(date('H', $mktimeDate)) . 'h' .  date('i', $mktimeDate);
			//$currentTime = intval($currentHour)+$currentOffset . 'h' . $aTime[1];
		}
		
		// TODO: Add DateChangeTime parameter.
		
		$data = $dataProvider->getDepartureBoardByStopPointForLine($stopPointExternalCode, $lineExternalCode, $currentDateFormatted, $forward);
		$aLines = $data['LineList']->ToArray();
		$line = $aLines[0];
		
		$markers = array(
			'PREFIXID' => $this->pObj->prefixId,
			'LINE_PICTO' => $this->pObj->pictoLine->getlinepicto($line->code /*$line->externalCode*/, 'Navitia'),
			'DATE_SEL' => $currentDate,
			'HOUR_SEL' => $currentTime,
			'HOUR_LESS_TEXT' => $this->pObj->pi_getLL('hourLess'),
			'HOUR_MORE_TEXT' => $this->pObj->pi_getLL('hourMore'),
			'HOUR' => '',
			'HOUR_LESS_URL' => $this->pObj->pi_linkTP_keepPIvars_url(array('hourOffset' => $currentOffset-1)),
			'HOUR_MORE_URL' => $this->pObj->pi_linkTP_keepPIvars_url(array('hourOffset' => $currentOffset+1)),
			'ACTION_URL' => $this->pObj->pi_linkTP_keepPIvars_url(array('hourOffset' => null)),
			'HIDDEN_FIELDS' => $this->pObj->getHiddenFields(),
			'ERROR' => '',
			'STOPPOINT_LABEL' => $this->pObj->pi_getLL('stoppoint'),
			'BOOKMARK_LABEL' => $this->pObj->pi_getLL('bookmark_add'),
		);
		
		if($this->pObj->pictoLine->getlinepicto($line->code /*$line->externalCode*/, 'Navitia')) {
			$markers['LINE_PICTO'] = $this->pObj->pictoLine->getlinepicto($line->code /*$line->externalCode*/, 'Navitia');
		}
		else {
			$markers['LINE_PICTO'] = $this->pObj->pi_getLL('line') . ' ' . $line->code;
		}
		
		$params = t3lib_div::_GET($this->pObj->prefixId);
		foreach ($params as $name => $value) {
			if (is_array($value))
				continue;
			if (in_array($name, array('date', 'hour', 'hourOffset')))
				continue;
			$markers['HIDDEN_FIELDS'] .= '<input type="hidden" name="' . htmlspecialchars($this->pObj->prefixId) . '[' . htmlspecialchars($name) . ']" value="' . htmlspecialchars($value) . '" />';
		}
		
		if(!$data['StopPointList']->Count() && !$data['StopList']->Count()) {
			$markers['ERROR'] = $this->pObj->pi_getLL('error');
		}

		if (tx_icslibnavitia_Debug::IsDebugEnabled()) {
			$markers['HOUR_LESS_URL'] .= '&' . $this->pObj->debug_param . '=' . t3lib_div::_GP($this->pObj->debugParam);
			$markers['HOUR_MORE_URL'] .= '&' . $this->pObj->debug_param . '=' . t3lib_div::_GP($this->pObj->debugParam);
			$markers['ACTION_URL'] .= '&' . $this->pObj->debug_param . '=' . t3lib_div::_GP($this->pObj->debugParam);
		}
		
		if ($forward) {
			$markers['DIRECTION_NAME'] = $line->forward->name;
		}
		else {
			$markers['DIRECTION_NAME'] = $line->backward->name;
		}
		
		$stopPoint = $data['StopPointList']->ToArray();
		
		$markers['STOP_NAME'] = $stopPoint[0]->name;
		
		if (t3lib_extMgm::isLoaded('ics_bookmarks')) {
			$libsBookmarks = t3lib_div::makeInstance('tx_icsbookmarks_libs');
			$bookmarks = $libsBookmarks->getBookmarks($this->pObj->extKey);
			
			$bookmarkData = array(
				'lineExternalCode' => $lineExternalCode,
				'stopAreaExternalCode' => $stopPoint[0]->stopArea->externalCode,
				'forward' => $forward,
			);
			$redirect = $this->pObj->pi_linkTP_keepPIvars_url();
			
			if(isset($bookmarks[serialize($bookmarkData)])) {
				$url = $libsBookmarks->getURL(tx_icsbookmarks_libs::ACTION_DELETE, $this->pObj->extKey, serialize($bookmarkData), $redirect);
				$markers['BOOKMARK_LABEL'] = $this->pObj->pi_getLL('bookmark_delete');
			}
			else {
				$url = $libsBookmarks->getURL(tx_icsbookmarks_libs::ACTION_ADD, $this->pObj->extKey, serialize($bookmarkData), $redirect);
			}
			$markers['BOOKMARK_URL'] = $url;
		}
		
		foreach ($data['StopList']->ToArray() as $stop) {
			if (!in_array($stop->stopTime->hour, $hours)) {
				$hours[] = $stop->stopTime->hour;
			}
			
			$schedules[$stop->stopTime->hour]['minute'][] = $stop->stopTime->minute;
			$schedules[$stop->stopTime->hour]['destination'][] = $stop->destination;
			$schedules[$stop->stopTime->hour]['comment'][] = $stop->comment->ExternalCode;
		}
		
		if (is_array($hours) && count($hours)) {
			foreach ($hours as $index => $hour) {
				if (($hour >= $currentHour) && !$aHours) {
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
				elseif (!$aHours && $hour == $hours[count($hours)-1] && intval($hour+1) == $currentHour) { // le cas où on a pas d'horaires mais seulement pour h-1
					if (!is_null($hours[$index])) {
						$hourToShow[] = $hours[$index]; 
					}
				}
			}
		}
		
		if (is_array($hourToShow) && count($hourToShow)) {
			foreach ($hourToShow as $hour) {
				$hoursToShowTemplate = $this->pObj->cObj->getSubpart($template, '###HOUR_COLUMNS###');
				$markers['HOUR'] = $hour;
				$hoursToShowContent .= $this->pObj->cObj->substituteMarkerArray($hoursToShowTemplate, $markers, '###|###');
			}
			$template = $this->pObj->cObj->substituteSubpart($template, '###HOUR_COLUMNS###', $hoursToShowContent);
			
			if (is_array($schedules) && count($schedules)) {
				foreach($schedules as $hour => $schedule) {
					if(t3lib_div::inArray($hourToShow, $hour)) {
						$aNbLines[] = count($schedule['minute']);
					}
				}
				$nbLines = max($aNbLines);
			}
		}
		
		if ($nbLines) {
			$templateLine = $this->pObj->cObj->getSubpart($template, '###SCHEDULES_LINE###');
			for ($index=0;$index<$nbLines;$index++) {
				$markers['EVENODD'] = '';
				$minsContent = '';
				foreach ($hourToShow as $hour) {
					$minutesTemplate = $this->pObj->cObj->getSubpart($templateLine, '###MIN_COLUMNS###');
					
					if ($index<count($schedules[$hour]['minute'])) {
						$markers['MIN'] = $schedules[$hour]['minute'][$index] . $this->aDestination[$schedules[$hour]['destination'][$index]] . $this->aDestination[$schedules[$hour]['comment'][$index]];
					}
					else {
						$markers['MIN'] = '';
					}
					$minsContent .= $this->pObj->cObj->substituteMarkerArray($minutesTemplate, $markers, '###|###');
				}
				$lineContent .= $this->pObj->cObj->substituteSubpart($templateLine, '###MIN_COLUMNS###', $minsContent);
			}
		}
		elseif($data['StopPointList']->Count() && $data['StopList']->Count()) {
			$markers['ERROR'] = $this->pObj->pi_getLL('error_no_more_schedules');
		}
		
		if($data['DestinationList']->Count()) {
			$index = 0;
			foreach ($data['DestinationList']->ToArray() as $destination) {
				$destinationTemplate = $this->pObj->cObj->getSubpart($template, '###DESTINATION_LIST###');
				$markers['TERMINUS'] = $this->pObj->pi_getLL('terminus');
				$markers['DESTINATION'] = $destination->name;
				$markers['DESTINATIONPOS'] = $this->aDestination[$index];
				$destinationContent .= $this->pObj->cObj->substituteMarkerArray($destinationTemplate, $markers, '###|###');
				$index++;
			}
		}
		
		/*if($data['CommentList']->Count()) {
			$index = 0;
			foreach ($data['CommentList']->ToArray() as $comment) {
				$commentTemplate = $this->pObj->cObj->getSubpart($template, '###COMMENT_LIST###');
				$markers['COMMENT_NAME'] = $comment->name;
				$markers['COMMENTPOS'] = $this->aDestination[$data['DestinationList']->Count() -1 + $index];
				$commentContent .= $this->pObj->cObj->substituteMarkerArray($commentTemplate, $markers, '###|###');
				$index++;
			}
		}*/
		
		$template = $this->pObj->cObj->substituteSubpart($template, '###COMMENT_LIST###', $commentContent);
		$template = $this->pObj->cObj->substituteSubpart($template, '###DESTINATION_LIST###', $destinationContent);
		$template = $this->pObj->cObj->substituteSubpart($template, '###SCHEDULES_LINE###', $lineContent);
		$content .= $this->pObj->cObj->substituteMarkerArray($template, $markers, '###|###');
		return $content;
	}
	
}